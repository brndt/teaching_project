Feature: Authorized search test resource by id

  Scenario: search test resource by id when all inputs are valid
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
      | id                                   | unitId                               | name        | description      | content      | created                   | modified                  | status    | questions                                                                                                                                                                                                                                  |
      | 6caf1483-92b9-4c67-b1fc-d0945052d1c3 | 5fd6fb25-a2a9-4376-af45-3c48508acd16 | random unit | some description | some content | 2020-07-14T13:54:13+00:00 | 2020-07-14T13:54:13+00:00 | published | [{"question":"hola como estas","answers":[{"answer":"bien","isCorrect":true},{"answer":"mal","isCorrect":false}]},{"question":"hola que tal","answers":[{"answer":"perfecto","isCorrect":true},{"answer":"fatal","isCorrect":false}]}] |
    And there are test resource student answers with the following details:
      | id                                   | resourceId                           | studentId                            | points | teacherComment | created                   | modified                  | status    | assumptions                                                                                                                                                                                                                                                                                            |
      | 4b71acce-eeda-475c-a4a2-6ac2fe472a7e | 6caf1483-92b9-4c67-b1fc-d0945052d1c3 | ab0642f0-ac7f-406a-903e-1c5480e7b7f8 | 10/10  | some comment   | 2020-07-14T13:54:13+00:00 | 2020-07-14T13:54:13+00:00 | published | [{"question":"hola como estas","student_assumption":"bien","answers":[{"answer":"bien","isCorrect":true},{"answer":"mal","isCorrect":false}]},{"question":"hola que tal","student_assumption":"perfecto","answers":[{"answer":"perfecto","isCorrect":true},{"answer":"fatal","isCorrect":false}]}] |
    And there are course permissions with the following details:
      | id                                   | courseId                             | studentId                            | created                   | modified                  | status    |
      | 5a79de58-3cd0-4c4d-8d33-cb5aee5eb84a | ea4f0311-4ffc-4fd0-9b4a-f2743565d851 | ab0642f0-ac7f-406a-903e-1c5480e7b7f8 | 2020-07-14T13:54:13+00:00 | 2020-07-14T13:54:13+00:00 | published |
    And I am authenticated as "nikita@lasalle.es" with "123456Aq" password
    When I send a GET request to "/api/v1/panel/test_resource_student_permission?resourceId=6caf1483-92b9-4c67-b1fc-d0945052d1c3&studentId=ab0642f0-ac7f-406a-903e-1c5480e7b7f8"
    Then the response status code should be 200
    And the response content should be:
        """
        {
          "id": "4b71acce-eeda-475c-a4a2-6ac2fe472a7e",
          "resourceId": "6caf1483-92b9-4c67-b1fc-d0945052d1c3",
          "studentId": "ab0642f0-ac7f-406a-903e-1c5480e7b7f8",
          "points": "10\/10",
          "teacherComment": "some comment",
          "created": "2020-07-14T13:54:13+00:00",
          "modified": "2020-07-14T13:54:13+00:00",
          "status": "published",
          "assumptions": [
            {
              "question": "hola como estas",
              "student_assumption": "bien",
              "answers": [
                {
                  "answer": "bien",
                  "isCorrect":true
                },
                {
                  "answer":"mal",
                  "isCorrect":false
                }
               ]
            },
            {
              "question": "hola que tal",
              "student_assumption": "perfecto",
              "answers": [
                {
                  "answer": "perfecto",
                  "isCorrect": true
                },
                {
                  "answer": "fatal",
                  "isCorrect":false
                }
              ]
            }
          ]
        }
        """
