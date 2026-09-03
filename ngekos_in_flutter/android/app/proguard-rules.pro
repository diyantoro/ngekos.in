# Firebase (FirebaseMessaging / google-services)
-keep class com.google.firebase.** { *; }
-keep class com.google.android.gms.** { *; }
-dontwarn com.google.firebase.**

# Flutter engine
-keep class io.flutter.** { *; }
-dontwarn io.flutter.embedding.**

# permission_handler (Pigeon-generated class)
-keep class com.baseflow.permissionhandler.** { *; }
-dontwarn com.baseflow.permissionhandler.**
