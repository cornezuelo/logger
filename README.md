# logger
Simple logger and profiler PHP class

# Usage
```
<?php
$logger = new Logger('/dir/file.log', 2); //Instantiate the Logger class, with a level 2 of logging. It will log everything on the path /dir/file.log
$logger->track('track_1'); //Profiles the script at this point, capturing CPU, memory usage, and the time that has passed since the instantiation of the class.
$logger->log('This is a fatal error',1); //Level 1 = Fatal Error. It will log the string into the log file.
$logger->log('This is a warning',2); //Level 2 = Warning. It will log the string into the log file.
$logger->log('This is information',3); //Level 3 = Information. It won't log anything in the file, as the Logger class was instantiated with a level 2 of logging. We would need to set the level to 3 to also log those messages.
$logger->track('track_2'); //Profiles the script at this point, capturing CPU, memory usage, and the time that has passed since the instantiation of the class.
$logger->log_track('track_1'); //Dumps the profile of track_1 into the log
$logger->log_track(); //Dumps all the profiles into the log
?>
```
