import 'dart:convert';
import 'package:web_socket_channel/web_socket_channel.dart';
import 'package:flutter/material.dart';

class NotificationService {
  static final NotificationService _instance = NotificationService._internal();
  factory NotificationService() => _instance;
  NotificationService._internal();

  WebSocketChannel? _channel;
  bool _isConnected = false;

  void connect(String ip, Function(String) onNotification) {
    if (_isConnected) return;

    try {
      // Reverb/Pusher compatible raw websocket connection
      // We use the IP and port 8080 from the reverb:start command
      _channel = WebSocketChannel.connect(
        Uri.parse('ws://$ip:8080/app/viotrack_key?protocol=7&client=js&version=8.4.0&flash=false'),
      );

      _channel!.stream.listen((message) {
        _isConnected = true;
        final data = jsonDecode(message);
        
        // Handle Pusher-style events
        if (data['event'] == 'App\\Events\\DashboardUpdated' || 
            data['event'] == 'App\\Events\\ViolationRecorded') {
          onNotification("Bagong update sa aming system! I-refresh ang dashboard.");
        }
        
        // Respond to ping to keep connection alive
        if (data['event'] == 'pusher:ping') {
          _channel!.sink.add(jsonEncode({'event': 'pusher:pong'}));
        }
      }, onError: (err) {
        _isConnected = false;
        _reconnect(ip, onNotification);
      }, onDone: () {
        _isConnected = false;
        _reconnect(ip, onNotification);
      });

      // Subscribe to public channel
      _channel!.sink.add(jsonEncode({
        'event': 'pusher:subscribe',
        'data': {'channel': 'dashboard-channel'}
      }));

    } catch (e) {
      _isConnected = false;
    }
  }

  void _reconnect(String ip, Function(String) onNotification) {
    Future.delayed(const Duration(seconds: 5), () => connect(ip, onNotification));
  }

  void disconnect() {
    _channel?.sink.close();
    _isConnected = false;
  }
}
