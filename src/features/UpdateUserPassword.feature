Feature: Update user password

  Scenario: Updating user password when all inputs are valid
    Given there are users with the following details:
      | id                                   | firstName | lastName    | email             | password | roles   |
      | 16bf6c6a-c855-4a36-a3dd-5b9f6d92c753 | nikita    | grichinenko | nikita@lasalle.es | 123456Aq | student |
    And I am authenticated as "nikita@lasalle.es" with "123456Aq" password
    When I send a PATCH request to "/api/v1/users/16bf6c6a-c855-4a36-a3dd-5b9f6d92c753/password" with body:
     """
    {
      "oldPassword": "123456Aq",
      "newPassword": "123456AqNewPassword"
    }
    """
    Then the response status code should be 200
    And the response content should be:
     """
    {
      "message": "Your account has been successfully changed"
    }
    """

  Scenario: Updating user password when I'm not such a user
    Given there are users with the following details:
      | id                                   | firstName | lastName    | email             | password | roles   |
      | 16bf6c6a-c855-4a36-a3dd-5b9f6d92c753 | nikita    | grichinenko | nikita@lasalle.es | 123456Aq | student |
      | cfe849f3-7832-435a-b484-83fabf530794 | irving    | cruz        | irving@lasalle.es | qwertY12 | admin   |
    And I am authenticated as "irving@lasalle.es" with "qwertY12" password
    When I send a PATCH request to "/api/v1/users/16bf6c6a-c855-4a36-a3dd-5b9f6d92c753/password" with body:
     """
    {
      "oldPassword": "123456Aq",
      "newPassword": "123456AqNewPassword"
    }
    """
    Then the response status code should be 403
    And the response content should be:
     """
    {
      "code": 403,
      "message": "You do not have permission to perform this action"
    }
    """
