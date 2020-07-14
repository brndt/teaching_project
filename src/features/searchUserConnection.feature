Feature: Search user connection

  Scenario: Search user connections when all inputs are valid
    Given there are Users with the following details:
      | id                                   | firstName | lastName    | email             | password | roles   |
      | 16bf6c6a-c855-4a36-a3dd-5b9f6d92c753 | nikita    | grichinenko | nikita@lasalle.es | 123456Aq | student |
      | cfe849f3-7832-435a-b484-83fabf530794 | irving    | cruz        | irving@lasalle.es | qwertY12 | teacher |
    And there are user connections with the following details:
      | studentId                            | teacherId                            | state  | specifierId                          |
      | 16bf6c6a-c855-4a36-a3dd-5b9f6d92c753 | cfe849f3-7832-435a-b484-83fabf530794 | pended | 16bf6c6a-c855-4a36-a3dd-5b9f6d92c753 |
    And I am authenticated as "nikita@lasalle.es" with "123456Aq" password
    When I send a GET request to "/api/v1/users/16bf6c6a-c855-4a36-a3dd-5b9f6d92c753/connections/cfe849f3-7832-435a-b484-83fabf530794"
    Then the response status code should be 200
    And the response content should be:
     """
    {
      "userId": "16bf6c6a-c855-4a36-a3dd-5b9f6d92c753",
      "friendId": "cfe849f3-7832-435a-b484-83fabf530794",
      "status": "pended",
      "specifierId": "16bf6c6a-c855-4a36-a3dd-5b9f6d92c753"
    }
    """
