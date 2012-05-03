Feature: FrontEndTransactionTests
  Tests to verify transactions are taking place successfully via ECheck.

Background:
  Given I am doing paypage transactions

    
  @javascript
  Scenario: Do a successful checkout and then capture the auth
  Given I am doing paypage auth
  And I am logged in as "gdake@litle.com" with the password "password"
    When I have "affluentvisa" in my cart
      And I press "Proceed to Checkout"
      And I press "Continue"
      And I press the "3rd" continue button
      And I choose "LEcheck"
      And I fill in "Bank routing number" with "123456000"
      And I fill in "Bank account number" with "123456000"
      And I select "Checking" from "Account type"
      And I press the "4th" continue button
    Then I press "Place Order"
    Then I should see "Thank you for your purchase"
      And I follow "Log Out"
    Given I am logged in as an administrator
    When I view "Sales" "Orders"
      Then I should see "Orders"
      And I click on the top row in Orders
        Then I should see "Order #"
        Then I should see "Litle ECheck"
      And I press "Invoice"
      And I select "Capture Online" from "invoice[capture_case]"
      And I press "Submit Invoice"
    Then I should see "The invoice has been created."
    And I follow "Log Out"


@javascript
Scenario: Backend ECheck auth checkout, then attempt to capture
   Given I am doing paypage auth
   And I am logged in as an administrator
   When I view "Sales" "Orders"
     Then I should see "Orders"
     And I press "Create New Order"
     And I click on the top row in CustomersList
     And I choose "English"
     And I press "Add Products"
     And I click on the top row in Product Table
     And I press "Add Selected Product(s) to Order"
     And I wait for the payments to appear
     And I follow "Get shipping methods and rates"
     And I choose "Fixed Shipping"
     And I choose "LEcheck"
     And I fill in "Bank routing number" with "123456000"
     And I fill in "Bank account number" with "123456000"
     And I select "Checking" from "Account type"
     And I press "Submit Order"
   When I view "Sales" "Orders"
     Then I should see "Orders"
     And I click on the top row in Orders
       Then I should see "Order #"
     And I press "Invoice"
     And I select "Capture Online" from "invoice[capture_case]"
     And I press "Submit Invoice"
   Then I should see "The invoice has been created."
   And I follow "Log Out"