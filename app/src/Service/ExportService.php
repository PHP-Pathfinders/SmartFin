<?php

namespace App\Service;

use App\Dto\Export\SearchDto;
use App\Entity\User;
use App\Repository\ExportRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Exception\SuspiciousOperationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

readonly class ExportService
{
    public function __construct(
        private ExportRepository $exportRepository,
        private Security $security,
        private string $exportsDir
    )
    {}
    public function search(?SearchDto $searchDto): array
    {
        /** @var User $user */
        $user = $this->security->getUser();
        $fileType = $searchDto->fileType ?? null;
        return $this->exportRepository->fetchExports(user: $user, fileType: $fileType);
    }

    public function download(string $fileName): string
    {
        /** @var User $user */
        $user = $this->security->getUser();

        // Get the file extension
        $fileInfo = pathinfo($fileName);
        if (!isset($fileInfo['extension'])) {
            throw new SuspiciousOperationException('File extension not found.');
        }
        $fileExtension = $fileInfo['extension'];

        // Check if that file belongs to logged-in user
        $export = $this->exportRepository->fetchExports(user:$user, fileName:  $fileName);
        if(empty($export)){
            throw new NotFoundHttpException('File not found');
        }

//        Dir path is named after extension (xlsx, pdf)
        $filePath = $this->exportsDir .'/'.$fileExtension.'/'.$fileName;
//        Check if the file exists
        if (!file_exists($filePath)) {
            throw new NotFoundHttpException('File not found.');
        }

        return $filePath;
    }
}