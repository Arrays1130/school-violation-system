import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import 'dart:convert';
import '../api_service.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';

class FCMService {
  static final FirebaseMessaging _messaging = FirebaseMessaging.instance;
  static final FlutterLocalNotificationsPlugin _localNotifications = FlutterLocalNotificationsPlugin();

  static const AndroidNotificationChannel _channel = AndroidNotificationChannel(
    'high_importance_channel', // id
    'High Importance Notifications', // title
    description: 'This channel is used for important school violation alerts.', // description
    importance: Importance.max,
  );

  static Future<void> initialize() async {
    // 1. Initialzie Local Notifications for foreground
    const AndroidInitializationSettings initializationSettingsAndroid = AndroidInitializationSettings('@mipmap/ic_launcher');
    const InitializationSettings initializationSettings = InitializationSettings(android: initializationSettingsAndroid);
    await _localNotifications.initialize(initializationSettings);

    // Create the channel on Android
    await _localNotifications
        .resolvePlatformSpecificImplementation<AndroidFlutterLocalNotificationsPlugin>()
        ?.createNotificationChannel(_channel);

    // 2. Request Permission (for iOS/Android 13+)
    NotificationSettings settings = await _messaging.requestPermission(
      alert: true,
      badge: true,
      sound: true,
    );

    if (settings.authorizationStatus == AuthorizationStatus.authorized) {
      print('User granted notification permission');
      
      // 3. Get Token and Sync with Backend
      await syncTokenWithBackend();
      
      // 4. Setup Foreground Message Listener
      FirebaseMessaging.onMessage.listen((RemoteMessage message) {
        RemoteNotification? notification = message.notification;
        AndroidNotification? android = message.notification?.android;

        if (notification != null && android != null) {
          _localNotifications.show(
            notification.hashCode,
            notification.title,
            notification.body,
            NotificationDetails(
              android: AndroidNotificationDetails(
                _channel.id,
                _channel.name,
                channelDescription: _channel.description,
                icon: android.smallIcon,
                importance: Importance.max,
                priority: Priority.high,
                ticker: 'ticker',
              ),
            ),
          );
        }
      });
    }
  }

  static Future<void> syncTokenWithBackend() async {
    try {
      String? token = await _messaging.getToken();
      
      if (token != null) {
        print('FCM Token: $token');
        
        final prefs = await SharedPreferences.getInstance();
        final loginToken = prefs.getString('token');
        
        if (loginToken != null) {
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
            print('FCM Token synced successfully');
          } else {
            print('Failed to sync FCM Token: ${response.statusCode}');
          }
        }
      }
    } catch (e) {
      print('Error syncing FCM Token: $e');
    }
  }
}

@pragma('vm:entry-point')
Future<void> handleBackgroundMessage(RemoteMessage message) async {
  print("Handling a background message: ${message.messageId}");
}

