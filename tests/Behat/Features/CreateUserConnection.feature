Feature: Create user connection

  Scenario: Creating user connection when all inputs are valid
    Given there are users with the following details:
      | id                                   | firstName | lastName    | email             | password | roles   |
      | 16bf6c6a-c855-4a36-a3dd-5b9f6d92c753 | nikita    | grichinenko | nikita@lasalle.es | 123456Aq | student |
      | cfe849f3-7832-435a-b484-83fabf530794 | irving    | cruz        | irving@lasalle.es | qwertY12 | teacher |
    And I am authenticated as "nikita@lasalle.es" with "123456Aq" password
    When I send a POST request to "/api/v1/users/16bf6c6a-c855-4a36-a3dd-5b9f6d92c753/connections" with body:
     """
    {
      "friendId": "cfe849f3-7832-435a-b484-83fabf530794"
    }
    """
    Then the response status code should be 201
    And the response content should be:
     """
    {
      "message": "Request has been successfully sent to this user"
    }
    """

  Scenario: Creating user connection when i'm not specified teacher or student
    Given there are users with the following details:
      | id                                   | firstName | lastName      | email             | password | roles   |
      | 16bf6c6a-c855-4a36-a3dd-5b9f6d92c753 | nikita    | grichinenko   | nikita@lasalle.es | 123456Aq | student |
      | cfe849f3-7832-435a-b484-83fabf530794 | irving    | cruz          | irving@lasalle.es | qwertY12 | teacher |
      | 419b2c90-b52d-4c2d-8fb8-b6419dbd5b10 | ruben     | cougil grande | ruben@lasalle.es  | poiuyt99 | teacher |
    And I am authenticated as "ruben@lasalle.es" with "poiuyt99" password
    When I send a POST request to "/api/v1/users/16bf6c6a-c855-4a36-a3dd-5b9f6d92c753/connections" with body:
     """
    {
      "friendId": "cfe849f3-7832-435a-b484-83fabf530794"
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

  Scenario: Creating user connection when i'm not signed in
    Given there are users with the following details:
      | id                                   | firstName | lastName      | email             | password | roles   |
      | 16bf6c6a-c855-4a36-a3dd-5b9f6d92c753 | nikita    | grichinenko   | nikita@lasalle.es | 123456Aq | student |
      | cfe849f3-7832-435a-b484-83fabf530794 | irving    | cruz          | irving@lasalle.es | qwertY12 | teacher |
      | 419b2c90-b52d-4c2d-8fb8-b6419dbd5b10 | ruben     | cougil grande | ruben@lasalle.es  | poiuyt99 | teacher |
    When I send a POST request to "/api/v1/users/16bf6c6a-c855-4a36-a3dd-5b9f6d92c753/connections" with body:
     """
    {
      "friendId": "cfe849f3-7832-435a-b484-83fabf530794"
    }
    """
    Then the response status code should be 401
    And the response content should be:
    """
    {
      "code": 401,
      "message": "JWT Token not found"
    }
    """

  Scenario: Creating user connection when all inputs are valid but such connection already exists
    Given there are users with the following details:
      | id                                   | firstName | lastName    | email             | password | roles   |
      | 16bf6c6a-c855-4a36-a3dd-5b9f6d92c753 | nikita    | grichinenko | nikita@lasalle.es | 123456Aq | student |
      | cfe849f3-7832-435a-b484-83fabf530794 | irving    | cruz        | irving@lasalle.es | qwertY12 | teacher |
    And there are user connections with the following details:
      | studentId                            | teacherId                            | state  | specifierId                          |
      | 16bf6c6a-c855-4a36-a3dd-5b9f6d92c753 | cfe849f3-7832-435a-b484-83fabf530794 | pended | 16bf6c6a-c855-4a36-a3dd-5b9f6d92c753 |
    And I am authenticated as "nikita@lasalle.es" with "123456Aq" password
    When I send a POST request to "/api/v1/users/16bf6c6a-c855-4a36-a3dd-5b9f6d92c753/connections" with body:
     """
    {
      "friendId": "cfe849f3-7832-435a-b484-83fabf530794"
    }
    """
    Then the response status code should be 400
    And the response content should be:
     """
    {
      "code": 400,
      "message": "Connection already exists"
    }
    """
