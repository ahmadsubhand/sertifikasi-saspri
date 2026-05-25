<?php

namespace common\services;

use common\enums\ApprovalStatus;
use common\enums\CertificateGrade;
use common\enums\IndicatorScoreAttribute;
use common\enums\RequestResponse;
use common\models\form\AddMembersForm;
use common\models\form\ExternalReviewForm;
use common\models\form\RegisterSaspriKForm;
use common\models\SaspriK;
use common\models\User;
use common\models\Certification;
use common\models\form\CoordinatorChangeForm;
use common\models\form\RequestResponseForm;
use Exception;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\UnprocessableEntityHttpException;
use yii\web\UploadedFile;

class SaspriKService
{
    public static function findOrFail(int $id) {
        $saspri_k = SaspriK::findOne($id);
        if (!$saspri_k) {
            throw new NotFoundHttpException('SASPRI-K tidak ditemukan');
        }
        return $saspri_k;
    }

    public static function findMember(int $user_id, int $saspri_k_id): User
    {
        $user = User::findOne([
            'id' => $user_id,
            'saspri_k_id' => $saspri_k_id,
        ]);
        if (!$user) {
            throw new NotFoundHttpException('Anggota tidak ditemukan dalam SASPRI-K');
        }
        return $user;
    }

    public static function addMembers(AddMembersForm $data)
    {
        $saspri_k = UserService::findSaspriKAsCoordinatorOrFail();

        $valid_users = User::find()->availableForSaspriK()
            ->andWhere(['id' => $data->user_ids])
            ->select('username')
            ->column();

        if (count($valid_users) !== count($data->user_ids)) {
            throw new BadRequestHttpException('Beberapa anggota tidak valid atau sudah terdaftar di SASPRI-K lain');
        }

        $saspri_k->addMembers($data->user_ids);

        return $valid_users;
    }

    public static function removeMember(int $user_id)
    {
        $saspri_k = UserService::findSaspriKAsCoordinatorOrFail();
        $user = SaspriKService::findMember($user_id, $saspri_k->id);
        $user->removeUserFromSaspriK()->save();

        return $user;
    }

    public static function register(RegisterSaspriKForm $data)
    {
        $document_files = UploadedFile::getInstancesByName('saspri_k_documents');
        if (empty($document_files)) {
            throw new BadRequestHttpException(
                'Wajib menyertakan dokumen pendukung minimal sertifikasi tingkat Natalia'
            );
        }

        $document_types = $data->saspri_k_documents;
        if (count($document_files) !== count($document_types)) {
            throw new BadRequestHttpException('Tipe dokumen wajib disertakan');
        }

        $user = UserService::me();
        if ($user->saspri_k_id) {
            throw new UnprocessableEntityHttpException('Anda sudah tergabung dalam SASPRI-K');
        }

        /** @var SaspriK $saspri_k */
        $saspri_k;
        try {
            $saspri_k = UserService::findSaspriKAsCoordinatorOrFail();
        } catch(Exception $error) {
            if ($error instanceof ForbiddenHttpException) {
                $saspri_k = new SaspriK();
            } else {
                throw $error;
            }
        }

        if ($saspri_k->request_status === ApprovalStatus::PENDING) {
            throw new UnprocessableEntityHttpException(
                'SASPRI-Kawasan sudah pernah didaftarkan dan masih dalam proses tinjauan SASPRI-Nasional'
            );
        }

        $saspri_k->setAttributes($data->attributes);
        $saspri_k->requestRegistration($user->id)->save();

        $saspri_k->deleteOldDocuments();
        $saspri_k->uploadNewDocuments($document_files, $document_types);

        if (!$saspri_k->getCertifications()->exists()) {
            $saspri_k->createCertificationForNewSaspriK()->save();
        }

        return [
            ...$saspri_k,
            'district' => $saspri_k->district,
        ];
    }

    public static function saveRegistration(int $saspri_k_id, ExternalReviewForm $data)
    {
        $saspri_k = SaspriKService::findOrFail($saspri_k_id)->isRequestRegistrationPending();

        /** @var Certification $certification */
        $certification = $saspri_k->getCertifications()->one();
        $certification->saveScores($data->indicator_scores, IndicatorScoreAttribute::EXTERNAL_REVIEW);

        return $certification;
    }

    public static function registrationRequestResponse(int $saspri_k_id, RequestResponseForm $data)
    {
        $saspri_k = SaspriKService::findOrFail($saspri_k_id)->isRequestRegistrationPending();

        if ($data->action === RequestResponse::APPROVE) {
            /** @var Certification $certification */
            $certification = $saspri_k->getCertifications()->one();
            $certification->ensureAllScoresFilled(IndicatorScoreAttribute::EXTERNAL_REVIEW)
                ->calculateTotalScore(IndicatorScoreAttribute::EXTERNAL_REVIEW)
                ->setGrade()
                ->submitExternalReview()
                ->generateCertificationCode()
                ->setNextCertificationDueDateToNow()
                ->save();
            if (
                $certification->grade === CertificateGrade::BC ||
                $certification->grade === CertificateGrade::C
            ) {
                throw new UnprocessableEntityHttpException(
                    'Nilai sertifikasi tidak memenuhi syarat kelulusan. Silakan tolak pengajuan sertifikasi ini'
                );
            }

            $coordinator = $saspri_k->coordinator;
            $coordinator->promoteToCoordinator();
            $saspri_k->addMembers([$coordinator->id]);

            $saspri_k->approveRegistration();
        } elseif ($data->action === RequestResponse::REJECT) {
            if (!$data->rejection_reason) {
                throw new BadRequestHttpException('Wajib menyertakan alasan penolakan');
            }
            $saspri_k->rejectRegistration($data->rejection_reason);
        } else {
            throw new BadRequestHttpException('Wajib memilih antara setuju atau tolak');
        }

        $saspri_k->save();
        return $saspri_k;
    }

    public static function changeCoordinator(CoordinatorChangeForm $data)
    {
        $saspri_k = UserService::findSaspriKAsCoordinatorOrFail();

        if ($saspri_k->change_status === ApprovalStatus::PENDING) {
            throw new UnprocessableEntityHttpException(
                'Pergantian wali sudah pernah diajukan dan masih dalam proses tinjauan SASPRI-Nasional'
            );
        }
        $new_coordinator = SaspriKService::findMember($data->new_coordinator_id, $saspri_k->id);
        $saspri_k->requestCoordinatorChange($new_coordinator->id, $data->change_request_reason)
            ->save();

        return [
            ...$saspri_k,
            'district' => $saspri_k->district,
        ];
    }

    public static function coordinatorChangeResponse(int $saspri_k_id, RequestResponseForm $data)
    {
        $saspri_k = SaspriKService::findOrFail($saspri_k_id)->isCoordinatorChangePending();

        if ($data->action === RequestResponse::APPROVE) {
            $old_coordinator = UserService::detail($saspri_k->coordinator_id);
            $old_coordinator->demoteFromCoordinator();

            $new_coordinator = UserService::detail($saspri_k->new_coordinator_id);
            $new_coordinator->promoteToCoordinator();

            $saspri_k->approveCoordinatorChange();
        } elseif ($data->action === RequestResponse::REJECT) {
            if (!$data->rejection_reason) {
                throw new BadRequestHttpException('Wajib menyertakan alasan penolakan');
            }
            $saspri_k->rejectCoordinatorChange($data->rejection_reason);
        } else {
            throw new BadRequestHttpException('Wajib memilih antara setuju atau tolak');
        }

        $saspri_k->save();
        return $saspri_k;
    }
}