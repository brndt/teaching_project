Feature: Search user

  Scenario: Searching user when all inputs are valid
    Given there are users with the following details:
      | id                                   | firstName | lastName    | email             | password | roles   | image      | education | experience | created                   |
      | 16bf6c6a-c855-4a36-a3dd-5b9f6d92c753 | nikita    | grichinenko | nikita@lasalle.es | 123456Aq | student | avatar.jpg | la salle  | 10 years   | 2020-07-14T17:39:01+00:00 |
    When I send a GET request to "/api/v1/users/16bf6c6a-c855-4a36-a3dd-5b9f6d92c753"
    Then the response status code should be 200
    And the response content should be:
     """
    {
      "id": "16bf6c6a-c855-4a36-a3dd-5b9f6d92c753",
      "firstName": "nikita",
      "lastName": "grichinenko",
      "roles": ["student"],
      "created": "2020-07-14T17:39:01+00:00",
      "image": "avatar.jpg",
      "education": "la salle",
      "experience": "10 years"
    }
    """

  Scenario: Searching user when user doesn't exist
    When I send a GET request to "/api/v1/users/16bf6c6a-c855-4a36-a3dd-5b9f6d92c753"
    Then the response status code should be 404
    And the response content should be:
    """
    {
      "code": 404,
      "message": "User was not found"
    }
    """
