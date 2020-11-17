# Schoology - Chris L - QA Automation Interview - Take Home#
*11/17/2020*

This PHP project was prepared using Selenium & Composer on an Eclipse IDE. The selenium test lives in the Schoology_Create_Course_Test.php file, which tests the Create Course workflow at [schoology](https://app.schoology.com/login “Title”). I ran the test on a selenium standalone server which can bind with a Chrome or Firefox/geckodriver. To run the tests, user needs to download PHP 7.4 (7.4.12), Java, Composer, and a webdriver of your choice.  I followed instructions posted [here](https://www.lambdatest.com/blog/selenium-php-tutorial/ “Title”).
After downloading the project folder, run the command “composer require” – which looks at the dependencies listed in your composer.json file and sets up your project accordingly.  Next, start your standalone selenium server by running the command “java -jar selenium-server-standalone-3.141.59.jar -port 7478” – I specified this port in the test because the default 4444 wasn’t working for me.  I was able to run the tests from the command line using the following command “vendor\bin\phpunit --debug Schoology_Create_Course_Test.php”.

***Exercise 1: In Schoology, teachers create courses which is then used to add assignments for the students. For this exercise, we would like you to write an automated test that will cover the happy path for creating a course.***

Here is a [sample flow of a course creation in Schoology](https://drive.google.com/file/d/1Lzho0O9gbqH_rwU7dP5Mt2EXwkiBE8Ro/view?usp=sharing “Title”).

Additional Resources: [How to create a course](https://support.schoology.com/hc/en-us/articles/201001943-Creating-a-Course “Title”)
