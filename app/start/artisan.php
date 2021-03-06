<?php

/*
|--------------------------------------------------------------------------
| Register The Artisan Commands
|--------------------------------------------------------------------------
|
| Each available Artisan command must be registered with the console so
| that it is available to be called. We'll register every command so
| the console gets access to each of the command object instances.
|
*/

Artisan::add(new AlertGithubActivity);
Artisan::add(new AlertPackagistActivity);
Artisan::add(new CacheRokuScreensaver);
Artisan::add(new CleanUpDownloadedFiles);
Artisan::add(new EmailRandomWorkout);
Artisan::add(new OfflinerLaracastVideosCommand);
Artisan::add(new OfflinerPocketVideosCommand);
Artisan::add(new OfflinerPushVideosCommand);
