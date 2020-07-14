Feature: Password reset request

  Scenario: Send password reset confirmation by email when all inputs are valid
    Given there are users with the following details:
      | id                                   | firstName | lastName    | email             | password | roles |
      | 16bf6c6a-c855-4a36-a3dd-5b9f6d92c753 | nikita    | grichinenko | nikita@lasalle.es | 123456Aq | admin |
    When I send a POST request to "/api/v1/users/password_resetting" with body:
    """
    {
      "email": "nikita@lasalle.es"
    }
    """
    Then the response content should be:
    """
    {
      "message": "An email has been sent. It contains a link you must click to reset your password."
    }
    """
    And the response status code should be 200
