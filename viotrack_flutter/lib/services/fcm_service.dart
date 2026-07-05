import 'dart:async';
import 'dart:convert';
import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter/widgets.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import 'package:http/http.dart' as http;
import '../api_service.dart';
import 'auth_storage_service.dart';
import 'notification_poller.dart';
import 'push_navigation_service.dart';

class FCMService {
  FCMService._();

  static final FirebaseMessaging _messaging = FirebaseMessaging.instance;
  static final FlutterLocalNotificationsPlugin _localNotifications =
      FlutterLocalNotificationsPlugin();

  static const AndroidNotificationChannel _channel = AndroidNotificationChannel(
    'high_importance_channel',
    'High Importance Notifications',
    description: 'School violation and hearing alerts for deans.',
    importance: Importance.max,
  );

  static bool _handlersRegistered = false;

  static Future<void> initialize() async {
    const androidSettings = AndroidInitializationSettings('@mipmap/ic_launcher');
    const initSettings = InitializationSettings(android: androidSettings);

    await _localNotifications.initialize(
      initSettings,
      onDidReceiveNotificationResponse: _onLocalNotificationTap,
    );

    await _localNotifications
        .resolvePlatformSpecificImplementation<
          AndroidFlutterLocalNotificationsPlugin
        >()
        ?.createNotificationChannel(_channel);

    _registerInteractionHandlers();

    final settings = await _messaging.requestPermission(
      alert: true,
      badge: true,
      sound: true,
    );

    final allowed =
        settings.authorizationStatus == AuthorizationStatus.authorized ||
        settings.authorizationStatus == AuthorizationStatus.provisional;

    if (!allowed) {
      debugPrint('FCM permission not granted: ${settings.authorizationStatus}');
    }

    FirebaseMessaging.onMessage.listen(_onForegroundMessage);
    _messaging.onTokenRefresh.listen((_) => syncTokenWithBackend());

    await syncTokenWithBackend();
  }

  static void _registerInteractionHandlers() {
    if (_handlersRegistered) return;
    _handlersRegistered = true;

    FirebaseMessaging.onMessageOpenedApp.listen(
      PushNavigationService.handleRemoteMessage,
    );
  }

  static Future<void> handleLaunchNotification() async {
    final initial = await _messaging.getInitialMessage();
    if (initial != null) {
      PushNavigationService.handleRemoteMessage(initial);
    }
  }

  static void _onForegroundMessage(RemoteMessage message) {
    unawaited(
      NotificationPoller.instance.poll(immediate: true, refreshLists: true),
    );
    _showLocalNotification(message);
  }

  static Future<void> _showLocalNotification(RemoteMessage message) async {
    final notification = message.notification;
    if (notification == null) return;

    final android = notification.android;
    await _localNotifications.show(
      notification.hashCode,
      notification.title,
      notification.body,
      NotificationDetails(
        android: AndroidNotificationDetails(
          _channel.id,
          _channel.name,
          channelDescription: _channel.description,
          icon: android?.smallIcon ?? '@mipmap/ic_launcher',
          importance: Importance.max,
          priority: Priority.high,
        ),
      ),
      payload: message.data.isNotEmpty ? jsonEncode(message.data) : null,
    );
  }

  static void _onLocalNotificationTap(NotificationResponse response) {
    final payload = response.payload;
    if (payload == null || payload.isEmpty) return;

    try {
      final decoded = jsonDecode(payload);
      if (decoded is Map) {
        PushNavigationService.handleData(
          Map<String, dynamic>.from(decoded),
        );
      }
    } catch (e) {
      debugPrint('Invalid local notification payload: $e');
    }
  }

  static Future<void> syncTokenWithBackend() async {
    try {
      final token = await _messaging.getToken();
      if (token == null) return;

      final loginToken = await AuthStorageService.getToken();
      if (loginToken == null) return;

      final response = await http.post(
        Uri.parse('${ApiService.baseUrl}/mobile/update-fcm-token'),
        headers: {
          'Accept': 'application/json',
          'Authorization': 'Bearer $loginToken',
          'Content-Type': 'application/json',
        },
        body: jsonEncode({'fcm_token': token}),
      );

      if (response.statusCode == 200) {
        debugPrint('FCM token synced with backend');
      } else {
        debugPrint('FCM token sync failed: ${response.statusCode}');
      }
    } catch (e) {
      debugPrint('Error syncing FCM token: $e');
    }
  }
}

@pragma('vm:entry-point')
Future<void> handleBackgroundMessage(RemoteMessage message) async {
  WidgetsFlutterBinding.ensureInitialized();
  await Firebase.initializeApp();
  debugPrint('Background FCM message: ${message.messageId}');
}
