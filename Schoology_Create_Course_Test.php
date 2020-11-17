<?php
require 'vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
// use Facebook\WebDriver\WebDriverOptions;
use Facebook\WebDriver\WebDriverSelect;

class Schoology_Create_Course_Test extends TestCase
{

    protected $webDriver;

    public function build_firefox_capabilities()
    {
        $capabilities = DesiredCapabilities::firefox();
        return $capabilities;
    }

    public function build_chrome_capabilities()
    {
        $capabilities = DesiredCapabilities::chrome();
        return $capabilities;
    }

    public function setUp(): void
    {
        $capabilities = $this->build_firefox_capabilities();
        // $capabilities = $this->build_chrome_capabilities();
        /*
         * Download the Selenium Server 3.141.59 from
         * https://selenium-release.storage.googleapis.com/3.141/selenium-server-standalone-3.141.59.jar
         */
        $this->webDriver = RemoteWebDriver::create('http://localhost:7478/wd/hub', $capabilities);
    }

    public function tearDown(): void
    {
        $this->webDriver->quit();
    }

    /*
     * @test
     */
    public function test_Create_New_Course()
    {
        $userName = 'hberger2@mudu1';
        $password = 'Password';
        $newCourseName = "New Demonstration Course";
        $sectionOneName = "Section 1";
        $sectionTwoName = "Section 2";
        $subjectMatter = "Technology";
        $levelSelect = "12";
        // added because course codes are saved, even when deleted - need to be unique
        $num = rand(10000, 90000);
        $courseCode = "DEMO" . $num;
        $sectionOneCode = "1";
        $sectionTwoSchoolCode = "DEMO1-1";

        // driver -> schoology
        $this->webDriver->get("https://app.schoology.com/login");
        $this->webDriver->manage()
            ->window()
            ->maximize();
        sleep(3);

        // Enter userName
        $emailUsernameInput = $this->webDriver->findElement(WebDriverBy::cssSelector("input#edit-mail"));
        $this->assertEquals("Email or Username", $emailUsernameInput->getAttribute("placeholder"), "FAIL: Incorrect Placeholder Found On Log in -> Username");
        $emailUsernameInput->click();
        $emailUsernameInput->sendKeys($userName);

        // Enter password
        $passwordInput = $this->webDriver->findElement(WebDriverBy::cssSelector("input#edit-pass"));
        $this->assertEquals("Password", $passwordInput->getAttribute("placeholder"), "FAIL: Incorrect Placeholder Found On Log in -> Password");
        $passwordInput->click();
        $passwordInput->sendKeys($password);

        // Login
        $loginButton = $this->webDriver->findElement(WebDriverBy::cssSelector("input#edit-submit"));
        $this->assertEquals("Log in", $loginButton->getAttribute("value"), "FAIL: Incorrect Value Found On Log in -> Log in Button");
        $this->assertTrue($loginButton->isEnabled(), "FAIL: Login Button is not Enabled after entering username & password");
        $loginButton->submit();
        // $loginButton->click();

        // Load Home Page
        $this->webDriver->wait(10, 500)->until(function ($driver) {
            // $this->webDriver->findElement(WebDriverBy::cssSelector("div#header"))->isDisplayed();
            $headerElement = $this->webDriver->findElement(WebDriverBy::cssSelector("div#header"));
            // return count($headerElements) > 0;
            return $headerElement->isDisplayed();
        });
        sleep(3);
        $this->assertEquals('Home | Schoology', $this->webDriver->getTitle());
        echo "PASS:  Successful Login - On Schoology Home Page\n";

        $this->navigate_To_Courses();

        // Click on Create Course button
        $courseButton = $this->webDriver->findElement(WebDriverBy::cssSelector("div.course-action-btns a.link-btn:nth-of-type(2)"));
        $this->assertEquals('Create Course', $courseButton->getText());
        $courseButton->click();

        // Wait for Create Course Modal to appear - Create New Course
        $this->webDriver->wait(10, 500)->until(function ($driver) {
            $createCourseModal = $this->webDriver->findElement(WebDriverBy::cssSelector("div.popups-title"));
            return $createCourseModal->isDisplayed();
            // $createCourseModal = $this->webDriver->findElements(WebDriverBy::cssSelector("div.popups-title"));
            // return count($createCourseModal) > 0;
        });
        echo "PASS:  Clicked on '" . $courseButton->getText() . "' Button, Now seeing Create Course Modal\n";

        $createCourseModalTitle = $this->webDriver->findElement(WebDriverBy::cssSelector("div.popups-title div.title"));
        $this->assertEquals('Create Course', $createCourseModalTitle->getText());

        // Enter Name of new Course
        $createCourseName = $this->webDriver->findElement(WebDriverBy::cssSelector("div.popups-body input#edit-course-name"));
        $createCourseName->click()->clear();
        $createCourseName->sendKeys($newCourseName);

        // Enter Section 1 Name
        $sectionOne = $this->webDriver->findElement(WebDriverBy::cssSelector("input#edit-section-name-1"));
        $sectionOne->click()->clear();
        $sectionOne->sendKeys($sectionOneName);

        // Section 2 not yet displayed
        $this->assertFalse($this->webDriver->findElement(WebDriverBy::cssSelector("input#edit-section-name-2"))
            ->isDisplayed(), "FAIL:  Section 2 is displayed before clicking on Add Section \n");

        // Click on Add Section button
        $addSection = $this->webDriver->findElement(WebDriverBy::cssSelector("div.popups-body div.add-button-wrapper span#section-add-btn"));
        $this->assertEquals('Add Section', $addSection->getText());
        $addSection->click();

        // Wait for Section 2 to Show Up
        $this->webDriver->wait(10, 500)->until(function ($driver) {
            $sectionTwo = $this->webDriver->findElement(WebDriverBy::cssSelector("input#edit-section-name-2"));
            return $sectionTwo->isDisplayed();
            // $sectionTwo = $this->webDriver->findElements(WebDriverBy::cssSelector("input#edit-section-name-2"));
            // return count($sectionTwo) > 0;
        });

        // Enter Section 2 Name
        $sectionTwo = $this->webDriver->findElement(WebDriverBy::cssSelector("input#edit-section-name-2"));
        $sectionTwo->click()->clear();
        $sectionTwo->sendKeys($sectionTwoName);

        // Section 2 should have a delete button next to it - clickable
        $sectionTwoDelete = $this->webDriver->findElement(WebDriverBy::cssSelector("div#csm-edit-section-2 div.edit-section-name-controls span#section-delete-btn-2"));
        $this->assertTrue($sectionTwoDelete->isEnabled(), "FAIL:  Delete Section 2 button is not clickable");

        // Now seeing Section Warning
        $expectedWarning = "Linked Sections share their admins, grade setups, materials and profiles. Member enrollments will still belong to their respective sections.";
        $sectionWarning = $this->webDriver->findElement(WebDriverBy::cssSelector("div#linked-sections-warning"));
        $this->assertEquals($expectedWarning, $sectionWarning->getText());

        // Confirm Expected Options in Subject Select Dropdown - Select Technology
        $selectSubject = $this->webDriver->findElement(WebDriverBy::cssSelector('select#edit-subject-area'));
        $selectSubArea = new WebDriverSelect($selectSubject);
        $selectSubOptions = $selectSubArea->getOptions();
        $selectSubArea->selectByVisibleText($subjectMatter);

        // Confirm Expected Options
        $expectedSubjectOptions = array(
            "",
            "Other",
            "Health & Physical Education",
            "Language Arts",
            "Mathematics",
            "Professional Development",
            "Science",
            "Social Studies",
            "Special Education",
            "Technology",
            "Arts"
        );

        $this->assertEquals(count($expectedSubjectOptions), count($selectSubOptions), "FAIL:  Did not find expected # of options in Subject Dropdown in Create Course Modal \n");

        for ($x = 0; $x < count($selectSubOptions); $x ++) {
            $this->assertEquals($expectedSubjectOptions[$x], $selectSubOptions[$x]->getText());
        }
        echo "PASS:  Found expected options in Subject Dropdown in Create Course Modal \n";

        // Confirm Technology isSelected
        $this->assertTrue($selectSubArea->getFirstSelectedOption()
            ->getText() == $subjectMatter);

        // Confirm Expected Options in Level Select Dropdown - Select 12th grade
        $selectLevel = $this->webDriver->findElement(WebDriverBy::cssSelector('select#edit-grade-level-range-start'));
        $selectLvl = new WebDriverSelect($selectLevel);
        $selectLvlOptions = $selectLvl->getOptions();
        $selectLvl->selectByVisibleText($levelSelect);

        // </optgroup><optgroup label="Primary/Secondary">
        // </optgroup><optgroup label="Higher Education">

        // Confirm expected Level options:
        $expectedLevelOptions = array(
            "",
            "None",
            "Pre-K",
            "K",
            "1",
            "2",
            "3",
            "4",
            "5",
            "6",
            "7",
            "8",
            "9",
            "10",
            "11",
            "12",
            "Undergraduate",
            "Graduate"
        );

        $this->assertEquals(count($expectedLevelOptions), count($selectLvlOptions), "FAIL:  Did not find expected # of options in Level Dropdown in Create Course Modal \n");

        for ($x = 0; $x < count($selectLvlOptions); $x ++) {
            $this->assertEquals($expectedLevelOptions[$x], $selectLvlOptions[$x]->getText());
        }
        echo "PASS:  Found expected options in Level Dropdown in Create Course Modal \n";

        // Confirm 12 isSelected
        $this->assertTrue($selectLvl->getFirstSelectedOption()
            ->getText() == $levelSelect);

        // Select forever almost Grading Period
        $foreverAlmost = $this->webDriver->findElement(WebDriverBy::cssSelector("div.existing-grading-period-item:nth-of-type(3) input.form-checkbox"));
        $foreverAlmost->click();
        $this->assertTrue($foreverAlmost->isSelected(), "FAIL:  Forever Almost Checkbox is not selected");

        // Course Code not yet displayed
        $this->assertFalse($this->webDriver->findElement(WebDriverBy::cssSelector("input#edit-course-code"))
            ->isDisplayed(), "FAIL:  Edit Course Code Input is displayed before clicking on Advanced \n");
        // Section 1 Code not yet displayed
        $this->assertFalse($this->webDriver->findElement(WebDriverBy::cssSelector("div#section-code-wrapper-1"))
            ->isDisplayed(), "FAIL:  Section 1 Code Wrapper is displayed before clicking on Advanced \n");
        // Section 2 Code not yet displayed
        $this->assertFalse($this->webDriver->findElement(WebDriverBy::cssSelector("div#section-code-wrapper-2"))
            ->isDisplayed(), "FAIL:  Section 2 Code Wrapper is displayed before clicking on Advanced \n");

        // Select Advanced Checkbox
        $advanced = $this->webDriver->findElement(WebDriverBy::cssSelector("input#edit-show-advanced"));
        $advanced->click();
        $this->assertTrue($advanced->isSelected(), "FAIL:  Advanced Checkbox is not selected");

        // Course Code now displayed
        $this->assertTrue($this->webDriver->findElement(WebDriverBy::cssSelector("input#edit-course-code"))
            ->isDisplayed(), "FAIL:  Edit Course Code Input is displayed before clicking on Advanced \n");
        // Section 1 Code now displayed
        $this->assertTrue($this->webDriver->findElement(WebDriverBy::cssSelector("div#section-code-wrapper-1"))
            ->isDisplayed(), "FAIL:  Section 1 Code Wrapper is displayed before clicking on Advanced \n");
        // Section 2 Code now displayed
        $this->assertTrue($this->webDriver->findElement(WebDriverBy::cssSelector("div#section-code-wrapper-2"))
            ->isDisplayed(), "FAIL:  Section 2 Code Wrapper is displayed before clicking on Advanced \n");

        // Enter Unique Course Code
        $courseCodeInput = $this->webDriver->findElement(WebDriverBy::cssSelector("input#edit-course-code"));
        $courseCodeInput->click()->clear();
        $courseCodeInput->sendKeys($courseCode);

        // Enter Section 1 Code
        $secOneCodeInput = $this->webDriver->findElement(WebDriverBy::cssSelector("input#edit-section-code-1"));
        $secOneCodeInput->click()->clear();
        $secOneCodeInput->sendKeys($sectionOneCode);

        // Confirm Section One Code is not using Section School Code
        $secOneCodeToggleLink = $this->webDriver->findElement(WebDriverBy::cssSelector("div#section-code-wrapper-1 div.s-course-code-id-toggle span"));
        $this->assertEquals("Use Section School Code", $secOneCodeToggleLink->getText());

        // Confirm Section Two Code is not using Section Code
        $secTwoCodeToggleLink = $this->webDriver->findElement(WebDriverBy::cssSelector("div#section-code-wrapper-2 div.s-course-code-id-toggle span"));
        $this->assertEquals("Use Section School Code", $secTwoCodeToggleLink->getText());
        $secTwoCodeToggleLink->click();
        sleep(1);
        $toggledSecTwoCodeToggleLink = $this->webDriver->findElement(WebDriverBy::cssSelector("div#school-code-wrapper-2 div.s-course-code-id-toggle span"));
        $this->assertEquals("Use Section Code", $toggledSecTwoCodeToggleLink->getText());

        // Enter Section 2 Code
        $secTwoCodeInput = $this->webDriver->findElement(WebDriverBy::cssSelector("input#edit-section-school-code-2"));
        $secTwoCodeInput->click()->clear();
        $this->assertTrue($secTwoCodeInput->getAttribute("prefill") == "Section School Code must be unique by organization");
        $secTwoCodeInput->sendKeys($sectionTwoSchoolCode);

        sleep(5);
        // Create new Course
        $createButton = $this->webDriver->findElement(WebDriverBy::cssSelector("div.submit-buttons input#edit-submit"));
        $this->assertTrue($createButton->isEnabled(), "FAIL: Create Course Button is not Enabled after entering all required data \n");
        $this->assertEquals("Create", $createButton->getAttribute("value"), "FAIL: Create Course Button incorrectly labeled \n");
        $createButton->click();

        sleep(3);
        $this->webDriver->wait(10, 500)->until(function ($driver) {
            $newCourseTitle = $this->webDriver->findElement(WebDriverBy::cssSelector("h2.page-title a.sExtlink-processed"));
            return $newCourseTitle->getText() == "New Demonstration Course: Section 1, Section 2";
        });
        echo "PASS:  New Course Created! \n";

        // navigate back to courses page, so test can delete newly created course
        $this->navigate_To_Courses();

        // Delete Course
        $courseSettings = $this->webDriver->findElement(WebDriverBy::cssSelector("ul.mycourses li.course-item:nth-of-type(3) span.action-links-unfold-text"));
        $courseSettings->click();
        sleep(3);
        $deleteCourse = $this->webDriver->findElement(WebDriverBy::cssSelector("ul.mycourses li.course-item:nth-of-type(3) li.action-delete"));
        $deleteCourse->click();

        // Confirm Delete
        $this->webDriver->wait(10, 500)->until(function ($driver) {
            $confirmDelete = $this->webDriver->findElement(WebDriverBy::cssSelector("div.popups-body input#edit-submit"));
            return $confirmDelete->isDisplayed();
        });
        $confirmDelete = $this->webDriver->findElement(WebDriverBy::cssSelector("div.popups-body input#edit-submit"));
        $confirmDelete->click();

        // sleep(2);
        // Course Deleted
        $this->webDriver->wait(10, 500)->until(function ($driver) {
            $message = $this->webDriver->findElement(WebDriverBy::cssSelector("div.popup-messages-wrapper div.message-text"));
            return $message->getText() == "Section Section 1, Section 2 has been deleted.";
        });
        echo "PASS: New Course Deleted\n";
        echo "PASS: SCHOOLOGY CREATE COURSE TEST";
    }

    // function that navigates "COURSES" > to "My Courses"
    public function navigate_To_Courses()
    {
        // Click on Course from Nav Bar - wait for shadow dropdown where "My Courses" link lives
        $courseButton = $this->webDriver->findElement(WebDriverBy::cssSelector("div#header nav ul:nth-child(1) li:nth-child(2) button"));
        $this->assertEquals("COURSES", $courseButton->getText(), "FAIL: Incorrect Value Found On Home Page -> Top Nav Row -> 'Course' Button");
        $this->assertTrue($courseButton->isEnabled(), "FAIL: My Course Button is not Enabled on Home Page");
        $courseButton->click();

        $this->webDriver->wait(10, 500)->until(function ($driver) {
            $dropdownElement = $this->webDriver->findElement(WebDriverBy::cssSelector("div.util-margin-top-negative-point-four-3GRLY.util-box-shadow-dropdown-2Bl9b"));
            return $dropdownElement->isDisplayed();
        });
        echo "PASS:  Clicked on '" . $courseButton->getText() . "' Button on Home Page -> Nav Row, Now seeing Shadow Dropdown\n";
        sleep(3);

        // Click on My Courses - wait for Manager Courses Buttons -> Create Course
        $myCoursesLink = $this->webDriver->findElement(WebDriverBy::linkText("My Courses"));
        $myCoursesLink->click();

        $this->webDriver->wait(10, 500)->until(function ($driver) {
            $courseButton = $this->webDriver->findElement(WebDriverBy::cssSelector("div.course-action-btns a.link-btn"));
            return $courseButton->isDisplayed();
        });
        echo "PASS:  Clicked on 'My Courses' Link, Now seeing Manager Courses Buttons\n";
    }
}
?>