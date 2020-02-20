# 📼 PHP FFmpeg Video Streaming
[![Build Status](https://travis-ci.org/aminyazdanpanah/PHP-FFmpeg-video-streaming.svg?branch=master)](https://travis-ci.org/aminyazdanpanah/PHP-FFmpeg-video-streaming)
[![Build status](https://img.shields.io/appveyor/ci/aminyazdanpanah/PHP-FFmpeg-video-streaming/master.svg?style=flat&logo=appveyor)](https://ci.appveyor.com/project/aminyazdanpanah/php-ffmpeg-video-streaming)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/aminyazdanpanah/PHP-FFmpeg-video-streaming/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/aminyazdanpanah/PHP-FFmpeg-video-streaming/?branch=master)
[![Total Downloads](https://img.shields.io/packagist/dt/aminyazdanpanah/php-ffmpeg-video-streaming.svg?style=flat)](https://packagist.org/packages/aminyazdanpanah/php-ffmpeg-video-streaming)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/aminyazdanpanah/php-ffmpeg-video-streaming?color=success)](https://packagist.org/packages/aminyazdanpanah/php-ffmpeg-video-streaming)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat)](https://github.com/aminyazdanpanah/PHP-FFmpeg-video-streaming/blob/master/LICENSE)

## Overview
This package provides integration with **[PHP-FFMpeg](https://github.com/PHP-FFMpeg/PHP-FFMpeg)** and packages media content for online streaming such as DASH and HLS. You can use **[DRM](https://en.wikipedia.org/wiki/Digital_rights_management)** for HLS packaging. There are several options to open a file from a cloud and save files to clouds as well.
- **[Full Documentation](https://video.aminyazdanpanah.com/)** is available describing all features and components.
- **[A complete example](https://video.aminyazdanpanah.com/start/example)** is provided. It contains server-side(Transcoding + cloud + progress + web socket) and client-side(progress bar + web socket + player).
- For using DRM and encryption, I **recommend** trying **[Shaka PHP](https://github.com/aminyazdanpanah/shaka-php)**, which is a great tool for this use case.

**Contents**
- [Requirements](#requirements)
- [Installation](#installation)
- [Quickstart](#quickstart)
  - [Configuration](#configuration)
  - [Opening a Resource](#opening-a-resource)
  - [DASH](#dash)
  - [HLS](#hls)
    - [DRM (Encrypted HLS)](#drm-encrypted-hls)
  - [Transcoding](#transcoding)
  - [Saving Files](#saving-files)
  - [Metadata Extraction](#metadata-extraction)
  - [Live](#live)
  - [Conversion](#conversion)
  - [Other Advanced Features](#other-advanced-features)
- [Asynchronous Task Execution](#asynchronous-task-execution)
- [Several Open Source Players](#several-open-source-players)
- [Contributing and Reporting Bugs](#contributing-and-reporting-bugs)
- [Credits](#credits)
- [License](#license)

## Requirements
1. This version of the package is only compatible with **[PHP 7.2](https://www.php.net/releases/)** or higher.

2. To use this package, you need to **[install the FFmpeg](https://ffmpeg.org/download.html)**. You will need both FFmpeg and FFProbe binaries to use it.

## Installation
Install the package via **[composer](https://getcomposer.org/)**:
``` bash
composer require aminyazdanpanah/php-ffmpeg-video-streaming
```
Alternatively, add the dependency directly to your `composer.json` file:
``` json
"require": {
    "aminyazdanpanah/php-ffmpeg-video-streaming": "^1.1"
}
```

## Quickstart
First of all, you need to include the package in your code:
``` php
require 'vendor/autoload.php'; // path to the autoload file
```

### Configuration
This package will autodetect FFmpeg and FFprobe binaries. If you want to give binary paths explicitly, you can pass an array as configuration. A Psr\Logger\LoggerInterface can also be passed to log binary executions.

``` php
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

$config = [
    'ffmpeg.binaries'  => '/usr/bin/ffmpeg',
    'ffprobe.binaries' => '/usr/bin/ffprobe',
    'timeout'          => 3600, // The timeout for the underlying process
    'ffmpeg.threads'   => 12,   // The number of threads that FFmpeg should use
];

$log = new Logger('FFmpeg_Streaming');
$log->pushHandler(new StreamHandler('/var/log/ffmpeg-streaming.log')); // path to log file
    
$ffmpeg = Streaming\FFMpeg::create($config, $log);
```

### Opening a Resource
There are two ways to open a file:

#### 1. From a FFmpeg supported resource
You can pass a local path of video(or a supported resource) to the `open` method:
``` php
$video = $ffmpeg->open('/var/www/media/videos/video.mp4');
```

Please see **[FFmpeg Protocols Documentation](https://ffmpeg.org/ffmpeg-protocols.html)** for opening a file from a supported resource such as `http`, `ftp`, `pipe`, `rtmp` and etc. 

**For example:** 
``` php
$video = $ffmpeg->open('https://www.aminyazdanpanah.com/PATH/TO/VIDEO.MP4');
```

#### 2. From Clouds
You can open a file from a cloud by passing an array of cloud configuration to the `openFromCloud` method. 

In **[this page](https://video.aminyazdanpanah.com/start/open-clouds)**, you will find some examples of opening a file from **[Amazon S3](https://aws.amazon.com/s3)**, **[Google Cloud Storage](https://console.cloud.google.com/storage)**, **[Microsoft Azure Storage](https://azure.microsoft.com/en-us/features/storage-explorer/)**, and a custom cloud. 
``` php
$video = $ffmpeg->openFromCloud($from_google_cloud);
```

### DASH
**[Dynamic Adaptive Streaming over HTTP (DASH)](http://dashif.org/)**, also known as MPEG-DASH, is an adaptive bitrate streaming technique that enables high quality streaming of media content over the Internet delivered from conventional HTTP web servers. [Learn more](https://en.wikipedia.org/wiki/Dynamic_Adaptive_Streaming_over_HTTP)
 
Create DASH files:
``` php
$video->DASH()
    ->HEVC() // Format of the video. Alternatives: X264() and VP9()
    ->autoGenerateRepresentations() // Auto generate representations
    ->setAdaption('id=0,streams=v id=1,streams=a') // Set the adaption.
    ->save(); // It can be passed a path to the method or it can be null
```
Generate representations manually:
``` php
use SyntaxEvolution\Streaming\Representation;

$r_144p  = (new Representation)->setKiloBitrate(95)->setResize(256, 144);
$r_240p  = (new Representation)->setKiloBitrate(150)->setResize(426, 240);
$r_360p  = (new Representation)->setKiloBitrate(276)->setResize(640, 360);
$r_480p  = (new Representation)->setKiloBitrate(750)->setResize(854, 480);
$r_720p  = (new Representation)->setKiloBitrate(2048)->setResize(1280, 720);
$r_1080p = (new Representation)->setKiloBitrate(4096)->setResize(1920, 1080);
$r_2k    = (new Representation)->setKiloBitrate(6144)->setResize(2560, 1440);
$r_4k    = (new Representation)->setKiloBitrate(17408)->setResize(3840, 2160);

$video->DASH()
    ->HEVC()
    ->addRepresentations([$r_144p, $r_240p, $r_360p, $r_480p, $r_720p, $r_1080p, $r_2k, $r_4k])
    ->setSegDuration(30) // Default value is 10 
    ->setAdaption('id=0,streams=v id=1,streams=a')
    ->save('/var/www/media/videos/dash-stream.mpd');
```

### HLS
**[HTTP Live Streaming (also known as HLS)](https://developer.apple.com/streaming/)** is an HTTP-based adaptive bitrate streaming communications protocol implemented by Apple Inc. as part of its QuickTime, Safari, OS X, and iOS software. Client implementations are also available in Microsoft Edge, Firefox and some versions of Google Chrome. Support is widespread in streaming media servers. [Learn more](https://en.wikipedia.org/wiki/HTTP_Live_Streaming)
 
Create HLS files:
``` php
$video->HLS()
    ->X264()
    ->autoGenerateRepresentations([720, 360]) // You can limit the number of representatons
    ->save();
```
Generate representations manually:
``` php
use SyntaxEvolution\Streaming\Representation;

$r_360p  = (new Representation)->setKiloBitrate(276)->setResize(640, 360);
$r_480p  = (new Representation)->setKiloBitrate(750)->setResize(854, 480);
$r_720p  = (new Representation)->setKiloBitrate(2048)->setResize(1280, 720);

$video->HLS()
    ->X264()
    ->setHlsBaseUrl('https://bucket.s3-us-west-1.amazonaws.com/videos') // Add a base URL
    ->addRepresentations([$r_360p, $r_480p, $r_720p])
    ->setHlsTime(5) // Set Hls Time. Default value is 10 
    ->setHlsAllowCache(false) // Default value is true 
    ->save();
```
**NOTE:** You cannot use HEVC and VP9 formats for HLS packaging.

#### DRM (Encrypted HLS)
The encryption process requires some kind of secret (key) together with an encryption algorithm. HLS uses AES in cipher block chaining (CBC) mode. This means each block is encrypted using the ciphertext of the preceding block. [Learn more](https://en.wikipedia.org/wiki/Block_cipher_mode_of_operation)

You must specify a path to save a random key to your local machine and also a URL(or a path) to access the key on your website(the key you will save must be accessible from your website). You must pass both these parameters to the `encryption` method:

##### Single Key
The following code generates a key for all TS files in a stream.

``` php
//A path you want to save a random key to your server
$save_to = '/home/public_html/PATH_TO_KEY_DIRECTORY/random_key.key';

//A URL (or a path) to access the key on your website
$url = 'https://www.aminyazdanpanah.com/PATH_TO_KEY_DIRECTORY/random_key.key';
// or $url = '/PATH_TO_KEY_DIRECTORY/random_key.key';

$video->HLS()
    ->X264()
    ->setTsSubDirectory('ts_files')// put all ts files in a subdirectory
    ->encryption($save_to, $url)
    ->autoGenerateRepresentations([1080, 480, 240])
    ->save('/var/www/media/videos/hls-stream.m3u8');
```

##### Key Rotation
This technique allows you to encrypt each TS file with a new encryption key. This can improve security and allows for more flexibility. You can also use a different key for each set of segments(e.g. if 10 TS files have been generated then rotate the key) or you can generate a new encryption key at every periodic time(e.g. every 10 seconds).

Please see **[the example](https://video.aminyazdanpanah.com/start?r=enc-hls#hls-encryption)** for more information.

**NOTE:** It is very important to protect your key(s) on your website using a token or a session/cookie(**It is highly recommended**).    

**NOTE:** However HLS supports AES encryption, which you can encrypt your streams, it is not a full DRM solution. If you want to use a full DRM solution, I recommend trying **[FairPlay Streaming](https://developer.apple.com/streaming/fps/)** solution which then securely exchange keys, and protect playback on devices.

### Transcoding
A format can also extend `FFMpeg\Format\ProgressableInterface` to get realtime information about the transcoding. 
``` php
$format = new Streaming\Format\HEVC();
$format->on('progress', function ($video, $format, $percentage){
    // You can update a field in your database or can log it to a file
    // You can also create a socket connection and show a progress bar to users
    echo sprintf("\rTranscoding...(%s%%) [%s%s]", $percentage, str_repeat('#', $percentage), str_repeat('-', (100 - $percentage)));
});

$video->DASH()
    ->setFormat($format)
    ->autoGenerateRepresentations()
    ->setAdaption('id=0,streams=v id=1,streams=a')
    ->save();
```

##### Output From a Terminal:
![transcoding](https://github.com/aminyazdanpanah/aminyazdanpanah.github.io/blob/master/video-streaming/transcoding.gif?raw=true "transcoding" )

### Saving Files
There are two ways to save your files.

#### 1. To a Local Path
You can pass a local path to the `save` method. If there was no directory in the path, then the package auto makes the directory.
``` php
$dash = $video->DASH()
            ->HEVC()
            ->autoGenerateRepresentations()
            ->setAdaption('id=0,streams=v id=1,streams=a');
            
$dash->save('/var/www/media/videos/dash-stream.mpd');
```
It can also be null. The default path to save files is the input path.
``` php
$hls = $video->HLS()
            ->X264()
            ->autoGenerateRepresentations();
            
$hls->save();
```
**NOTE:** If you open a file from a cloud and do not pass a path to save the file to your local machine, you will have to pass a local path to the `save` method.

#### 2. To Clouds
You can save your files to a cloud by passing an array of cloud configuration to the `save` method. 

In **[this page](https://video.aminyazdanpanah.com/start/open-clouds)**, you will find some examples of saving files to **[Amazon S3](https://aws.amazon.com/s3)**, **[Google Cloud Storage](https://console.cloud.google.com/storage)**, **[Microsoft Azure Storage](https://azure.microsoft.com/en-us/features/storage-explorer/)**, and a custom cloud. 
``` php
$dash->save(null, [$to_aws_cloud, $to_google_cloud, $to_microsoft_azure, $to_custom_cloud]);
``` 
A path can also be passed to save a copy of files to your local machine.
``` php
$hls->save('/var/www/media/videos/hls-stream.m3u8', [$to_google_cloud, $to_custom_cloud]);
```
**NOTE:** This option(Save To Clouds) is only valid for **[VOD](https://en.wikipedia.org/wiki/Video_on_demand)** (it does not support live streaming).

**Schema:** The relation is `one-to-many`

<p align="center"><img src="https://github.com/aminyazdanpanah/aminyazdanpanah.github.io/blob/master/video-streaming/video-streaming.gif?raw=true" width="100%"></p>

### Metadata Extraction
After saving files(wherever you saved them), you can extract the metadata from the video and streams. You can save these metadata to your database.
``` php
extract($hls->save());

echo $filename; // path to metadata.json
print_r($metadata); // print metadata -> it's an array
```
**NOTE:** It will not save metadata to clouds because of some security reasons.

### Live
You can pass a url(or a supported resource like `ftp`) to live method to upload all the segments files to the HTTP server(or other protocols) using the HTTP PUT method, and update the manifest files every refresh times.

If you want to save stream files to your local machine, please use the `save` method.

``` php
// DASH live
$dash->live('http://YOUR-WEBSITE.COM/live-stream/out.mpd');

// HLS live
$hls
    ->setMasterPlaylist('/var/www/stream/live-master-manifest.m3u8')
    ->live('http://YOUR-WEBSITE.COM/live-stream/out.m3u8');
```
**NOTE:** In the HLS method, you must upload the master manifest to the server manually. (Upload the `/var/www/stream/live-master-manifest.m3u8` file to the `http://YOUR-WEBSITE.COM`)

Please see **[FFmpeg Protocols Documentation](https://ffmpeg.org/ffmpeg-protocols.html)** for more information.

### Conversion
You can convert your stream to a file or to another stream protocols. You should pass a manifest of the stream to the `open` method:

#### 1. HLS To DASH
``` php
$stream = $ffmpeg->open('https://www.aminyazdanpanah.com/PATH/TO/HLS-MANIFEST.M3U8');

$stream->DASH()
    ->X264()
    ->addRepresentations([$r_360p, $r_480p]) 
    ->save('/var/www/media/dash-stream.mpd');
```

#### 2. DASH To HLS
``` php
$stream = $ffmpeg->open('https://www.aminyazdanpanah.com/PATH/TO/DASH-MANIFEST.MPD');

$stream->HLS()
           ->X264()
           ->autoGenerateRepresentations([720, 360])
           ->save('/var/www/media/hls-stream.m3u8');
```

#### 3. Stream(DASH or HLS) To File
``` php
$format = new Streaming\Format\X264();
$format->on('progress', function ($video, $format, $percentage){
    echo sprintf("\rTranscoding...(%s%%) [%s%s]", $percentage, str_repeat('#', $percentage), str_repeat('-', (100 - $percentage)));
});

$stream->stream2file()
           ->setFormat($format)
           ->save('/var/www/media/new-video.mp4');
```

### Other Advanced Features
You can easily use other advanced features in the **[PHP-FFMpeg](https://github.com/PHP-FFMpeg/PHP-FFMpeg)** library. In fact, when you open a file with the `open` method(or `openFromCloud`), it holds the Media object that belongs to the PHP-FFMpeg.
``` php
$ffmpeg = Streaming\FFMpeg::create()
$video = $$ffmpeg->openFromCloud($from_cloud, '/var/wwww/media/my/new/video.mp4');
```

#### Extracting image
You can extract a frame at any timecode using the `FFMpeg\Media\Video::frame` method.
``` php
$frame = $video->frame(FFMpeg\Coordinate\TimeCode::fromSeconds(42));
$frame->save('/var/www/media/videos/poster.jpg');
```
**NOTE:** You can use the image as a video's poster.

#### Gif
A gif is an animated image extracted from a sequence of the video.

You can save gif files using the FFMpeg\Media\Gif::save method.

``` php
$video
    ->gif(FFMpeg\Coordinate\TimeCode::fromSeconds(2), new FFMpeg\Coordinate\Dimension(640, 480), 3)
    ->save('/var/www/media/videos/animated_image.gif');
```
This method has a third optional boolean parameter, which is the duration of the animation. If you don't set it, you will get a fixed gif image.

**NOTE:** You can use the gif as a video's thumbnail.

To see more examples, please vist the **[PHP-FFMpeg Documentation](https://github.com/PHP-FFMpeg/PHP-FFMpeg)**  page.

## Asynchronous Task Execution
Packaging process will may take a while and it is recommended to run it in the background(or in a cloud e.g. Google Cloud). There are some libraries that you can use.
- **[Symphony(The Console Component)](https://github.com/symfony/console):** You can use this library to create command-line commands. Your console commands can be used for any recurring task, such as cronjobs, imports, or other batch jobs. [Learn more](https://symfony.com/doc/current/components/console.html#learn-more)

- **[Laravel(Queues)](https://github.com/illuminate/queue):** If you are using Laravel for development, Laravel Queues is a wonderful tool for this use case. It allows you to create a job and dispatch it. [Learn more](https://laravel.com/docs/6.0/queues)

- **[Google Cloud Tasks](https://github.com/googleapis/google-cloud-php-tasks):** Google Cloud Tasks is a fully managed service that allows you to manage the execution, dispatch, and delivery of a large number of distributed tasks. You can asynchronously perform work outside of a user request. [Learn more](https://cloud.google.com/tasks/)

**NOTE:** It is not necessary to use these libraries. It is just a suggestion. You can also create a script to create packaged video files and run a job in the cron job.  

## Several Open Source Players
You can use these libraries to play your streams.
- **WEB**
    - DASH and HLS: 
        - **[Video.js 7](https://github.com/videojs/video.js) - [videojs-http-streaming (VHS)](https://github.com/videojs/http-streaming)**
        - **[Plyr](https://github.com/sampotts/plyr)**
        - **[DPlayer](https://github.com/MoePlayer/DPlayer)**
        - **[MediaElement.js](https://github.com/mediaelement/mediaelement)**
        - **[Clappr](https://github.com/clappr/clappr)**
        - **[Shaka Player](https://github.com/google/shaka-player)**
        - **[Flowplayer](https://github.com/flowplayer/flowplayer)**
    - DASH:
        - **[dash.js](https://github.com/Dash-Industry-Forum/dash.js)**
    - HLS: 
        - **[hls.js](https://github.com/video-dev/hls.js)**
- **Android**
    - DASH and HLS: 
        - **[ExoPlayer](https://github.com/google/ExoPlayer)**
- **IOS**
    - DASH: 
        - **[MPEGDASH-iOS-Player](https://github.com/MPEGDASHPlayer/MPEGDASH-iOS-Player)**
    - HLS: 
        - **[Player](https://github.com/piemonte/Player)**
- **Windows, Linux, and macOS**
    - DASH and HLS:
        - **[FFmpeg(ffplay)](https://github.com/FFmpeg/FFmpeg)**
        - **[VLC media player](https://github.com/videolan/vlc)**

As you may know, **[IOS](https://www.apple.com/ios)** does not have native support for DASH. Although there are some libraries such as **[Viblast](https://github.com/Viblast/ios-player-sdk)** and **[MPEGDASH-iOS-Player](https://github.com/MPEGDASHPlayer/MPEGDASH-iOS-Player)** to support this technique, I have never tested them. So if you know any IOS player that supports DASH Stream and also works fine, please add it to the above list. 

**NOTE:** You should pass a manifest of stream(e.g. `https://www.aminyazdanpanah.com/PATH_TO_STREAM_DIRECTORY/dash-stream.mpd` or `/PATH_TO_STREAM_DIRECTORY/hls-stream.m3u8` ) to these players.

## Contributing and Reporting Bugs
I'd love your help in improving, correcting, adding to the specification.
Please **[file an issue](https://github.com/aminyazdanpanah/PHP-FFmpeg-video-streaming/issues)** or **[submit a pull request](https://github.com/aminyazdanpanah/PHP-FFmpeg-video-streaming/pulls)**.
- Please see **[Contributing File](https://github.com/aminyazdanpanah/PHP-FFmpeg-video-streaming/blob/master/CONTRIBUTING.md)** for more information.
- If you have any questions or you want to report a bug, please just **[file an issue](https://github.com/aminyazdanpanah/PHP-FFmpeg-video-streaming/issues)**
- If you discover a security vulnerability within this package, please see **[SECURITY File](https://github.com/aminyazdanpanah/PHP-FFmpeg-video-streaming/blob/master/SECURITY.md)** for more information.

**NOTE:** If you have any questions about this package or FFmpeg, please **DO NOT** send an email to me (or submit the contact form on my website). Emails regarding these issues **will be ignored**.

## Credits
- **[Amin Yazdanpanah](https://www.aminyazdanpanah.com/?u=github.com/aminyazdanpanah/PHP-FFmpeg-video-streaming)**
- **[All Contributors](https://github.com/aminyazdanpanah/PHP-FFmpeg-video-streaming/graphs/contributors)**

## License
The MIT License (MIT). Please see **[License File](https://github.com/aminyazdanpanah/PHP-FFmpeg-video-streaming/blob/master/LICENSE)** for more information.
