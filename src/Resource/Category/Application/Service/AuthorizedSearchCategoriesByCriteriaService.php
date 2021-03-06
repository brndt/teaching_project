<?php

declare(strict_types=1);

namespace LaSalle\StudentTeacher\Resource\Category\Application\Service;

use LaSalle\StudentTeacher\Resource\Category\Application\Request\AuthorizedSearchCategoriesByCriteriaRequest;
use LaSalle\StudentTeacher\Resource\Category\Application\Response\CategoryCollectionResponse;
use LaSalle\StudentTeacher\Resource\Category\Application\Response\CategoryResponse;
use LaSalle\StudentTeacher\Resource\Category\Domain\Aggregate\Category;
use LaSalle\StudentTeacher\Resource\Category\Domain\Repository\CategoryRepository;
use LaSalle\StudentTeacher\Shared\Domain\Criteria\Criteria;
use LaSalle\StudentTeacher\Shared\Domain\Criteria\Filters;
use LaSalle\StudentTeacher\Shared\Domain\Criteria\Operator;
use LaSalle\StudentTeacher\Shared\Domain\Criteria\Order;
use LaSalle\StudentTeacher\Shared\Domain\ValueObject\Uuid;
use LaSalle\StudentTeacher\User\User\Domain\Repository\UserRepository;
use LaSalle\StudentTeacher\User\User\Domain\Service\AuthorizationService;
use LaSalle\StudentTeacher\User\User\Domain\Service\UserService;

final class AuthorizedSearchCategoriesByCriteriaService
{
    private UserService $userService;

    public function __construct(
        UserRepository $userRepository,
        private CategoryRepository $categoryRepository,
        private AuthorizationService $authorizationService
    ) {
        $this->userService = new UserService($userRepository);
    }

    public function __invoke(AuthorizedSearchCategoriesByCriteriaRequest $request): CategoryCollectionResponse
    {
        $requestAuthorId = new Uuid($request->getRequestAuthorId());
        $requestAuthor = $this->userService->findUser($requestAuthorId);
        $this->authorizationService->ensureRequestAuthorIsAdmin($requestAuthor);

        $criteria = new Criteria(
            Filters::fromValues($request->getFilters()),
            Order::fromValues($request->getOrderBy(), $request->getOrder()),
            Operator::fromValue($request->getOperator()),
            $request->getOffset(),
            $request->getLimit()
        );

        $categories = $this->categoryRepository->matching($criteria);

        return new CategoryCollectionResponse(...$this->buildResponse(...$categories));
    }

    private function buildResponse(Category ...$categories): array
    {
        return array_map(
            static function (Category $category) {
                return new CategoryResponse(
                    $category->getId()->toString(),
                    $category->getName(),
                    $category->getStatus()->value(),
                );
            },
            $categories
        );
    }
}
