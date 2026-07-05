import 'dart:async';
import 'package:flutter/foundation.dart';
import '../api_service.dart';
import 'auth_storage_service.dart';

/// Computes the delay before the next poll attempt after failures.
@visibleForTesting
Duration computePollBackoff({
  required Duration baseInterval,
  required Duration maxInterval,
  required int consecutiveFailures,
  int maxSteps = 4,
}) {
  if (consecutiveFailures <= 0) return baseInterval;
  final step = consecutiveFailures.clamp(1, maxSteps);
  final multiplier = 1 << (step - 1);
  final ms = (baseInterval.inMilliseconds * multiplier).clamp(
    baseInterval.inMilliseconds,
    maxInterval.inMilliseconds,
  );
  return Duration(milliseconds: ms);
}

/// Central foreground poller for unread notification count with backoff and coalescing.
class NotificationPoller {
  NotificationPoller._();
  static final NotificationPoller instance = NotificationPoller._();

  static const _baseInterval = Duration(seconds: 15);
  static const _maxInterval = Duration(seconds: 120);

  final ValueNotifier<int> unreadCount = ValueNotifier<int>(0);
  final StreamController<void> listRefresh = StreamController<void>.broadcast();

  Timer? _timer;
  bool _running = false;
  bool _foreground = true;
  bool _pollInFlight = false;
  bool _pollAgain = false;
  bool _pollAgainRefreshLists = false;
  int _lastCount = 0;
  int _consecutiveFailures = 0;
  Duration _currentInterval = _baseInterval;
  bool _hasBaseline = false;
  VoidCallback? _offlineListener;

  bool get isRunning => _running;
  bool get hasBaseline => _hasBaseline;

  void start() {
    if (_running) return;
    _running = true;
    _attachOfflineListener();
    _schedulePoll(immediate: true);
  }

  void stop() {
    _detachOfflineListener();
    _timer?.cancel();
    _timer = null;
    _running = false;
    _pollInFlight = false;
    _pollAgain = false;
    _pollAgainRefreshLists = false;
  }

  void setForeground(bool value) {
    final wasBackground = !_foreground;
    _foreground = value;

    if (!value) {
      _timer?.cancel();
      _timer = null;
      return;
    }

    if (wasBackground) {
      _resetBackoff();
      _schedulePoll(immediate: true, refreshLists: true);
    }
  }

  Future<void> poll({bool immediate = false, bool refreshLists = false}) async {
    if (!_running) return;

    if (immediate) {
      _timer?.cancel();
      await _executePoll(refreshLists: refreshLists);
      if (_running && _foreground) {
        _schedulePoll();
      }
      return;
    }

    await _executePoll(refreshLists: refreshLists);
  }

  void _attachOfflineListener() {
    if (_offlineListener != null) return;
    _offlineListener = () {
      if (!_running || !_foreground) return;
      if (ApiService.isOfflineNotifier.value) return;

      _resetBackoff();
      _schedulePoll(immediate: true);
    };
    ApiService.isOfflineNotifier.addListener(_offlineListener!);
  }

  void _detachOfflineListener() {
    if (_offlineListener == null) return;
    ApiService.isOfflineNotifier.removeListener(_offlineListener!);
    _offlineListener = null;
  }

  void _schedulePoll({bool immediate = false, bool refreshLists = false}) {
    _timer?.cancel();
    if (!_running || !_foreground) return;

    final delay = immediate ? Duration.zero : _currentInterval;
    _timer = Timer(delay, () async {
      await _executePoll(refreshLists: refreshLists);
      if (_running && _foreground) {
        _schedulePoll();
      }
    });
  }

  Future<void> _executePoll({bool refreshLists = false}) async {
    if (!_running) return;

    if (ApiService.isOfflineNotifier.value) {
      _recordFailure();
      return;
    }

    if (!await AuthStorageService.hasToken()) return;

    if (_pollInFlight) {
      _pollAgain = true;
      _pollAgainRefreshLists =
          _pollAgainRefreshLists || refreshLists;
      return;
    }

    _pollInFlight = true;
    try {
      final count = await ApiService().getUnreadCount();
      _recordSuccess();
      _applyCount(count, refreshLists: refreshLists);
    } catch (e) {
      _recordFailure();
      if (e.toString().contains('Session expired')) {
        stop();
      }
    } finally {
      _pollInFlight = false;
      if (_pollAgain) {
        final againRefresh = _pollAgainRefreshLists;
        _pollAgain = false;
        _pollAgainRefreshLists = false;
        await _executePoll(refreshLists: againRefresh);
      }
    }
  }

  void _applyCount(int count, {bool refreshLists = false}) {
    if (!_hasBaseline) {
      _lastCount = count;
      _hasBaseline = true;
      unreadCount.value = count;
      if (refreshLists) {
        listRefresh.add(null);
      }
      return;
    }

    final previous = _lastCount;
    _lastCount = count;
    unreadCount.value = count;

    if (refreshLists || count != previous) {
      listRefresh.add(null);
    }
  }

  void _recordSuccess() {
    _consecutiveFailures = 0;
    _currentInterval = _baseInterval;
  }

  void _recordFailure() {
    _consecutiveFailures++;
    _currentInterval = computePollBackoff(
      baseInterval: _baseInterval,
      maxInterval: _maxInterval,
      consecutiveFailures: _consecutiveFailures,
    );
  }

  void _resetBackoff() {
    _consecutiveFailures = 0;
    _currentInterval = _baseInterval;
  }

  void dispose() {
    stop();
    listRefresh.close();
  }
}
