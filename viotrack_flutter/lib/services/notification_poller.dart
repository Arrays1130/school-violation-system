import 'dart:async';
import 'package:flutter/foundation.dart';
import '../api_service.dart';

/// Central foreground poller for unread notification count (~15s).
class NotificationPoller {
  NotificationPoller._();
  static final NotificationPoller instance = NotificationPoller._();

  static const _interval = Duration(seconds: 15);

  final ValueNotifier<int> unreadCount = ValueNotifier<int>(0);
  final StreamController<void> listRefresh = StreamController<void>.broadcast();

  Timer? _timer;
  bool _running = false;
  bool _foreground = true;
  int _lastCount = 0;
  bool _hasBaseline = false;

  bool get isRunning => _running;
  bool get hasBaseline => _hasBaseline;

  void start() {
    if (_running) return;
    _running = true;
    poll(immediate: true);
    _timer = Timer.periodic(_interval, (_) {
      if (_foreground) poll();
    });
  }

  void stop() {
    _timer?.cancel();
    _timer = null;
    _running = false;
  }

  void setForeground(bool value) {
    final wasBackground = !_foreground;
    _foreground = value;
    if (value && wasBackground) {
      poll(immediate: true);
    }
  }

  Future<void> poll({bool immediate = false}) async {
    if (!_running) return;
    try {
      final count = await ApiService().getUnreadCount();

      if (!_hasBaseline) {
        _lastCount = count;
        _hasBaseline = true;
        unreadCount.value = count;
        return;
      }

      final previous = _lastCount;
      _lastCount = count;
      unreadCount.value = count;

      if (count != previous) {
        listRefresh.add(null);
      }
    } catch (_) {}
  }

  void dispose() {
    stop();
    listRefresh.close();
  }
}
