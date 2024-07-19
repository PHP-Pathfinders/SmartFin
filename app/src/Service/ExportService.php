<?php

namespace App\Service;

use App\Dto\Export\SearchDto;
use App\Entity\User;
use App\Repository\ExportRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Finder\Exception\AccessDeniedException;

readonly class ExportService
{
    public function __construct(
        private ExportRepository $exportRepository,
        private Security $security,
    )
    {}
    public function search(SearchDto $searchDto): array
    {
        /** @var User $user */
        $user = $this->security->getUser();
        if (!$user || (int)$searchDto->userId !== $user->getId())
        {
           throw new AccessDeniedException('Wrong user id or user not authenticated');
        }
        return $this->exportRepository->fetchExports($user, $searchDto->fileType);
    }
}