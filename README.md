# WeatherViaPHP
A PHP file to get data from Weather Underground API and transfer it to PWS Weather

It is pretty straight forward, edit the file to fill in the API, IDs and password information. 
Upload it to your favorite (php compatible) webhost. A note of warning here, many free webhosts use javascript in order to filter out automated traffic (such is the kind this script thrives on).
Setup a Cronjob with at least 3 minutes between site hits (in order to fit within 500 free hits per day). https://cron-job.org is recommended since it is free and saves the last few responces for error tracking purposes.

If you wish to see the script in action, or use it for your own needs without needing to setup a server, check out http://wufyi.com where you can speficy operational parameters in the URL itself and start using it with a free cron job site.

Cheers,
Gene
