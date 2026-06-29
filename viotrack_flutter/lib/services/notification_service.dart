import 'dart:convert';
import 'package:web_socket_channel/web_socket_channel.dart';

class NotificationService {
  static final NotificationService _instance = NotificationService._internal();
  factory NotificationService() => _instance;
  NotificationService._internal();

  WebSocketChannel? _channel;
  bool _isConnected = false;

  /// WebSocket disabled on cloud hosts — prevents reconnect loops and crashes.
  void connect(String ip, Function(String) onNotification) {
    if (_isConnected) return;
    if (ip.contains('onrender.com') || ip.contains('127.0.0.1') || ip.contains('localhost')) {
      return;
    }

    try {
      _channel = WebSocketChannel.connect(
        Uri.parse('ws://$ip:8080/app/viotrack_key?protocol=7&client=js&version=8.4.0'),
      );

      _channel!.stream.listen(
        (message) {
          _isConnected = true;
          try {
            final data = jsonDecode(message);
            if (data is! Map) return;
            final event = data['event']?.toString() ?? '';
            if (event.contains('DashboardUpdated') || event.contains('ViolationRecorded')) {
              onNotification('May bagong update sa system.');
            }
            if (event == 'pusher:ping') {
              _channel!.sink.add(jsonEncode({'event': 'pusher:pong'}));
            }
          } catch (_) {}
        },
        onError: (_) => _isConnected = false,
        onDone: () => _isConnected = false,
        cancelOnError: true,
      );
    } catch (_) {
      _isConnected = false;
    }
  }

  void disconnect() {
    try {
      _channel?.sink.close();
    } catch (_) {}
    _isConnected = false;
  }
}
