Feature: Update unit

  Scenario: update unit when all inputs are valid
    Given there are users with the following details:
      | id                                   | firstName | lastName    | email             | password | roles   |
      | 16bf6c6a-c855-4a36-a3dd-5b9f6d92c753 | nikita    | grichinenko | nikita@lasalle.es | 123456Aq | admin   |
      | cfe849f3-7832-435a-b484-83fabf530794 | irving    | cruz        | irving@lasalle.es | qwertY12 | teacher |
    And there are categories with the following details:
      | id                                   | name     | status    |
      | b2c3532f-6629-435a-9908-63f9d3811ccd | language | published |
    And there are courses with the following details:
      | id                                   | categoryId                           | teacherId                            | name           | description      | level      | created                   | modified                  | status    |
      | cfe849f3-7832-435a-b484-83fabf530794 | b2c3532f-6629-435a-9908-63f9d3811ccd | 16bf6c6a-c855-4a36-a3dd-5b9f6d92c753 | spanish course | some description | some level | 2020-07-14T13:54:13+00:00 | 2020-07-14T13:54:13+00:00 | published |
    And there are units with the following details:
      | id                                   | courseId                             | name        | description      | level      | created                   | modified                  | status    |
      | cfe849f3-7832-435a-b484-83fabf530794 | cfe849f3-7832-435a-b484-83fabf530794 | random unit | some description | some level | 2020-07-14T13:54:13+00:00 | 2020-07-14T13:54:13+00:00 | published |
    And I am authenticated as "nikita@lasalle.es" with "123456Aq" password
    When I send a PATCH request to "/api/v1/panel/units/cfe849f3-7832-435a-b484-83fabf530794" with body:
    """
    {
      "courseId": "cfe849f3-7832-435a-b484-83fabf530794",
      "name": "random unit",
      "description": "random description",
      "level": "basic",
      "status": "published"
    }
    """
    Then the response status code should be 201
    And the response content should be:
    """
    {
      "message": "Unit has been successfully updated"
    }
    """

  Scenario: Update unit when unit doesn't exist
    Given there are users with the following details:
      | id                                   | firstName | lastName    | email             | password | roles   |
      | 16bf6c6a-c855-4a36-a3dd-5b9f6d92c753 | nikita    | grichinenko | nikita@lasalle.es | 123456Aq | admin   |
      | cfe849f3-7832-435a-b484-83fabf530794 | irving    | cruz        | irving@lasalle.es | qwertY12 | teacher |
    And there are categories with the following details:
      | id                                   | name     | status    |
      | b2c3532f-6629-435a-9908-63f9d3811ccd | language | published |
    And there are courses with the following details:
      | id                                   | categoryId                           | teacherId                            | name           | description      | level      | created                   | modified                  | status    |
      | cfe849f3-7832-435a-b484-83fabf530794 | b2c3532f-6629-435a-9908-63f9d3811ccd | 16bf6c6a-c855-4a36-a3dd-5b9f6d92c753 | spanish course | some description | some level | 2020-07-14T13:54:13+00:00 | 2020-07-14T13:54:13+00:00 | published |
    And I am authenticated as "nikita@lasalle.es" with "123456Aq" password
    When I send a PATCH request to "/api/v1/panel/units/cfe849f3-7832-435a-b484-83fabf530794" with body:
    """
    {
      "courseId": "cfe849f3-7832-435a-b484-83fabf530794",
      "name": "random unit",
      "description": "random description",
      "level": "basic",
      "status": "published"
    }
    """
    Then the response status code should be 400
    And the response content should be:
    """
    {
      "code": 400,
      "message": "Unit not found"
    }
    """
