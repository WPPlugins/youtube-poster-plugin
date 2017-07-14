=== YouTube Poster Plugin ===
Tags: YouTube, video, posts       
Stable tag: 0.7   
Requires at least: 2.8       
Tested up to: 2.9.2
Contributors: Keith Graham       


Runs searches on YouTube and automatically creates a blog post for each video found.

== Description ==

The idea behind the YouTube Posts Plugin was to create content directly from YouTube. I had some sites that were digests of interesting videos. Rather than cut and paste for each video I made a way to automate the creation of blog posts.

With this plugin you just select the videos that you want to place on the blog and press a button. All the videos will then be placed as separate blog posts. You can select if you want the videos to be dated with the current date and time, or you can use the date of the video as the post date.

The plugin executes a search on YouTube by keyword and category. It displays a list of all videos found. You check off the videos that you like and then the program will create blog posts for each video. It first checks to make sure that the video has not already been posted. I keeps a list of previous searches to make things quick and easy.

This is a quick and easy way to add content to your blog on those days when you don't have time to write 1,000 words.

The plugin require that you register with YouTube for an API application key. This allows you to run searches directly on the YouTube database and get back a list of videos. The API key is free and it takes only a minute to register. You can get the key at: http://code.google.com/apis/youtube/2.0/reference.html

There are two steps to installing the plugin. First download it or install it from the WordPress repository. Before you can use the plugin you must go into settings and enter an API key. Once the API key is entered there is a link under Posts for entering the YouTube posts.

== Installation ==

1. Download the plugin.
2. Upload the plugin to your wp-content/plugins directory.
3. Activate the plugin.
4. Enter yout API key on the YoutTube Poster settings page. (get the key at http://code.google.com/apis/youtube/2.0/reference.html ) 
5. Once a valid key is entered you can post YouTube videos under the Posts heading on the admin page.

This plugin requires at least PHP 5. and SimpleXML

if you are on a hosting platform that defaults to PHP 4, but offers PHP 5 you can add this line to your .htaccess file:

AddType x-mapp-php5 .php
or 
AddHandler application/x-httpd-php5 php

The first one worked for my 1and1.com accounts, and the second worked for my generic linux hosting with cpanel account.


== Changelog ==

= 0.7 =
* Fixed a problem with Wordpress 3.0.

= 0.6 =
* Added the option to add posts as draft. Added ability to add as pages instead of posts.

= 0.5 =
* initial release of working code. I need comments and recommendations for improvements. Please report any bugs.


== Support ==

This plugin is in active development. All feedback is welcome on <a href="http://www.blogseye.com/" title="Wordpress plugin: YouTube Poster Plugin">My Wordpress and other program development</a>.
