<?php

namespace Src\User\Infrastructure\Controller;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Src\Shared\Domain\Exception\BadRequestException;
use Src\Shared\Domain\ValueObject\CountryId;
use Src\Shared\Domain\ValueObject\UserId;
use Src\User\Application\UserCreator;
use Src\User\Domain\ValueObject\UserDetail;
use Src\User\Domain\ValueObject\UserDetailsFirstName;
use Src\User\Domain\ValueObject\UserDetailsLastName;
use Src\User\Domain\ValueObject\UserDetailsPhoneNumber;
use Src\User\Domain\ValueObject\UserEmail;
use Src\User\Domain\ValueObject\UserIsActive;

class UserPostController extends Controller
{


    public function __construct(
        private UserCreator $creator,
    ){}

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws BadRequestException
     */
    public function __invoke(Request $request): JsonResponse
    {
        $this->validateRequest($request);

        $userId = new UserId($request->route('userId'));

        $data = $request->post();
        $userEmail = new UserEmail($data['email']);
        $userIsActive = new UserIsActive($data['isActive']);
        $userDetail = $this->buildUserDetail($data);

        $this->creator->__invoke($userId, $userEmail, $userIsActive, $userDetail);

        return response()
            ->json(null)
            ->setStatusCode(201);

    }

    /**
     * @param Request $request
     * @throws BadRequestException
     */
    private function validateRequest(Request $request): void
    {
        $data = $request->post();
        $mandatoryFields = ["email", "isActive"];

        foreach ($mandatoryFields as $mandatoryField) {
            if (!array_key_exists($mandatoryField, $data)) {
                throw new BadRequestException([], "Mandatory fiels {$mandatoryField} not provided.");
            }
        }

    }

    /**
     * @param array $data
     * @return UserDetail|null
     * @throws BadRequestException
     */
    private function buildUserDetail(array $data): ?UserDetail
    {
        if (!array_key_exists('detail', $data)) {
            return null;
        }

        $detailData = $data['detail'];

        $necessaryFields = ['countryId', 'firstName', 'lastName', 'phoneNumber'];

        foreach ($necessaryFields as $necessaryField) {
            if (!array_key_exists($necessaryField, $detailData)) {
                return null;
            }
        }

        return new UserDetail(
            new CountryId($detailData["countryId"]),
            new UserDetailsFirstName($detailData["firstName"]),
            new UserDetailsLastName($detailData["lastName"]),
            new UserDetailsPhoneNumber($detailData["phoneNumber"])
        );
    }

}
