=== Raopress Chat - Firebase Chat for Visitors ===
Contributors: raoinfotech
Tags: Firebase, Chats, Manage Chats, Create Users, Send & receive, ChatmessagesFirebase, Chats, Manage Chats, Users, Send & receive, Chat messages,WordPress, WordPress Firebase Integration,Realtime Chat
Requires at least: 4.7
Tested up to: 6.4.2
Requires PHP: 7.0
Stable tag: 1.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Raopress Chat - Firebase Chat for Visitors is the first Real time Wordpress Chat Plugin that integrates with Firebase Chat

== Description ==
This plugin allows you to interact with your website users/visitors in real time. Admin/Specific role can manage chats from WP Admin. It is the first WordPress Plugin that integrates WordPress with Firebase Chat.

== Note ==
This plugin provides a service to manage real time chat conversation stored in firebase. This is the reason firebase libraries are called remotely.

== Raopress Chat Admin Mobile Application for IOS & Android
Raopress Chat Admin App provides a facility to respond your customers or subscribers using a mobile APP. Best part of this is that you do not need to login to WP amin dashboard to reply to the chat messages. [Buy now](https://licence-manager.raoinfo.tech/)

== Firebase Settings Configuration Steps ==
==Step 1 : Create Firebase Project 
1. Go to [Google Console](https://console.firebase.google.com/)
2. Click on Add Project
3. Select Parent Resource
4. Click on continue
5. Enable Google Analytics (Optional)
6. Choode or create Google Analytics Account if you enabled Google Analytics from Step 4
7. Click on Create Project
8. Click on Continue => This would redirect you to Project Overview Dashboard.

== Step2 : Create Realtime Database

1. Navigate to Build => Realtime Database
2. Click on Create Database
3. Choose the Realtime Database Location
4. Start in test mode
5. Realtime Database will be created
6. Go to Rules Tab and you can set up the date till the end of year to let your customers send chat messages.

== Step 3: Create Web APP
1. Navigate to Project Settings => General Tab => Your Apps Section (At the bottom)
2. Create Web App
3. Provide a App Name 
4. Click on Register App
5. Click on Continue to Console
6. Navigate to Project Settings => General Tab => Your Apps => SDK setup and Configuration => Config Option
7. Copy the content with curly braces in text editor and enclose JSON object key with "". 
For eg: apiKey: "AIzaSyDJOxnnKZmJWdSuQ7pCKvrC0qyFT27VJII" to
"apiKey": "AIzaSyDJOxnnKZmJWdSuQ7pCKvrC0qyFT27VJII"
After adjusting, it should like below with the associated project values
{
  "apiKey": "your-project-key",
  "authDomain": "your-project-authdomain",
  "databaseURL": "your-project-databaseurl",
  "projectId": "your-projectId",
  "storageBucket": "your-project-storageBucket",
  "messagingSenderId": "your-project-senderId",
  "appId": "your-project-appId",
  "measurementId": "your-project-measurementId"
}

Add the above object into Plugin Settings Page => General Tab => Firebase App Configuration field


== Step 4: Configure Firebase Database & Secret Keys
1. Navigate to  Google Console => Project Settings => Service Accounts (tab)
2. Click on Generate new private key button
3. Confirm and click on Generate Key
4. Json file will be downloaded automatically
5. Copy the file content to Plugin Settings Page => General Tab => Firebase DB Configuration

== Step 5: Configure Authentication to add an admin user from a Plugin Admin Dashboard Page
1. Navigate to Google Console =>  Build => Authentication 
2. Click on Get Started
3. Navigate to Sign in method tab
4. Select Native Providers => Email/Password
5. Go to Raopress Chat Plugin Home Page
6.  Register with your email & password which would act as an admin for the Chats received from your customers/subscribers

== Step 6: Enable Firebase Storage ==
If you want allow your customers to append a document/file, need to enable firebase storage from 
1. Navigate to Google Console => Build => Storage
2. Click on Get Started
3. Select start in test mode
4. Choose the Storage Location
5. Open Rules tab
6. Set date to  Next year to avoid adjusting the rules again and again.

== Step 7: Configure Widget Style
Configure Settings in Plugin Settings Page => Firebase Chat Widget Settings (Tab). 

You can set widget theme color according to your WP theme Primary Color and a Welcome message that you want your customers or subscribers to see when the chat initiates.




== Changelog ==
= 1.0.0 =
Initial release.

= 1.1.0 =
Add License Page for Admin Mobile App
Update readme with the Firebase Configuration Steps

= 1.2.0 =
Fix - Firebase realtime users update

= 1.2.1 =
Add - Auto prompt chat screen on first time visit
Admin can enable/disable the prompt

= 1.3 =
Add missing files for License Validation
Tested OK with Wordpress 6.4.2 