@mod @mod_grouptool @adminsetting @amc
Feature: Within a moodle instance, an administrator should be able to set the value for "Allow deregistration" for the entire Moodle installation.
  In order to define the adminsettings of an grouptool.
  As an admin
  I need to default values for grouptool settings.

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email |
      | teacher1 | Teacher | 1 | teacher1@example.com |
    And the following "courses" exist:
      | fullname | shortname | category | groupmode |
      | Course 1 | C1 | 0 | 1|
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | C1 | editingteacher |

  @javascript
  Scenario: Switch as an admin to the adminsettings of the module grouptool and change the value of "Allow deregistration" to "Yes". Then login as a teacher and add a new grouptool to a course and check whether the default value has changed.
    Given I log in as "admin"
    And I navigate to "Plugins > Activity modules > Grouptool" in site administration
    And I set the field "Allow self registration" to "1"
    And I set the field "Allow deregistration" to "1"
    And I press "Save changes"
    And I log out
    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I turn editing mode on
    And I add a "Grouptool" to section "1" and I fill the form with:
      | Grouptool name | Test grouptool name - Yes |
      | ID number | Test grouptool name - Yes |
      | Description | Add a grouptool to the current course |
    And I turn editing mode off
    When I am on the "Test grouptool name - Yes" Activity page logged in as admin
    And I navigate to "Settings" in current page administration
    And I expand all fieldsets
    Then the field "Allow deregistration" matches value "Yes"

    Then I log out

  @javascript
  Scenario: Switch as an admin to the adminsettings of the module grouptool and change the value of "Allow deregistration" to "No". Then login as a teacher and add a new grouptool to a course and check whether the default value has changed.
    Given I log in as "admin"
    And I navigate to "Plugins > Activity modules > Grouptool" in site administration
    And I set the field "Allow self registration" to "1"
    And I set the field "Allow deregistration" to ""
    And I press "Save changes"
    And I log out
    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I turn editing mode on
    And I add a "Grouptool" to section "1" and I fill the form with:
      | Grouptool name | Test grouptool name - No |
      | ID number | Test grouptool name - No |
      | Description | Add a grouptool to the current course |
    When I am on the "Test grouptool name - No" Activity page logged in as admin
    And I navigate to "Settings" in current page administration
    And I expand all fieldsets
    Then the field "Allow deregistration" matches value "No"
