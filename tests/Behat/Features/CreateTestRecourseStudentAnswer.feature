Feature: Create test resource student answer

  Scenario: Creating unit when all inputs are valid
    Given there are users with the following details:
      | id                                   | firstName | lastName    | email              | password | roles   |
      | 16bf6c6a-c855-4a36-a3dd-5b9f6d92c753 | nikita    | grichinenko | nikita@lasalle.es  | 123456Aq | admin   |
      | cfe849f3-7832-435a-b484-83fabf530794 | irving    | cruz        | irving@lasalle.es  | qwertY12 | teacher |
      | ab0642f0-ac7f-406a-903e-1c5480e7b7f8 | student   | luis        | student@lasalle.es | qwertY12 | student |
    And there are categories with the following details:
      | id                                   | name     | status    |
      | b2c3532f-6629-435a-9908-63f9d3811ccd | language | published |
    And there are courses with the following details:
      | id                                   | categoryId                           | teacherId                            | name           | description      | level      | created                   | modified                  | status    |
      | ea4f0311-4ffc-4fd0-9b4a-f2743565d851 | b2c3532f-6629-435a-9908-63f9d3811ccd | 16bf6c6a-c855-4a36-a3dd-5b9f6d92c753 | spanish course | some description | some level | 2020-07-14T13:54:13+00:00 | 2020-07-14T13:54:13+00:00 | published |
    And there are units with the following details:
      | id                                   | courseId                             | name        | description      | level      | created                   | modified                  | status    |
      | 5fd6fb25-a2a9-4376-af45-3c48508acd16 | ea4f0311-4ffc-4fd0-9b4a-f2743565d851 | random unit | some description | some level | 2020-07-14T13:54:13+00:00 | 2020-07-14T13:54:13+00:00 | published |
    And there are test resources with the following details:
      | id                                   | unitId                               | name        | description      | content      | created                   | modified                  | status    | questions                                                                                                                                                                                                                              |
      | 6caf1483-92b9-4c67-b1fc-d0945052d1c3 | 5fd6fb25-a2a9-4376-af45-3c48508acd16 | random unit | some description | some content | 2020-07-14T13:54:13+00:00 | 2020-07-14T13:54:13+00:00 | published | [{"question":"hola como estas","answers":[{"answer":"bien","isCorrect":true},{"answer":"mal","isCorrect":false}]},{"question":"hola que tal","answers":[{"answer":"perfecto","isCorrect":true},{"answer":"fatal","isCorrect":false}]}] |
    And there are course permissions with the following details:
      | id                                   | courseId                             | studentId                            | created                   | modified                  | status    |
      | 5a79de58-3cd0-4c4d-8d33-cb5aee5eb84a | ea4f0311-4ffc-4fd0-9b4a-f2743565d851 | ab0642f0-ac7f-406a-903e-1c5480e7b7f8 | 2020-07-14T13:54:13+00:00 | 2020-07-14T13:54:13+00:00 | published |
    And I am authenticated as "student@lasalle.es" with "qwertY12" password
    When I send a POST request to "/api/v1/panel/test_resource_student_permission" with body:
    """
    {
      "resourceId": "6caf1483-92b9-4c67-b1fc-d0945052d1c3",
      "points": "10/10",
      "teacherComment": "some comment",
      "status": "status",
      "assumptions": {
        "0": {
          "question": "hola como estas",
          "student_assumption": "bien"
        },
        "1": {
          "question": "hola que tal",
          "student_assumption": "mal"
        }
      }
    }
    """
    Then the response status code should be 201
    And the response content should be:
    """
    {
      "message": "Test resource student answer has been successfully created"
    }
    """
