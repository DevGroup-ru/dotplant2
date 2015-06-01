<?php

use app\backend\models\BackendMenu;
use app\models\Object;
use app\models\Property;
use app\models\PropertyGroup;
use app\models\PropertyHandler;
use app\models\ObjectPropertyGroup;
use app\modules\review\models\RatingItem;
use app\modules\review\models\RatingValues;
use app\modules\review\models\Review;
use yii\db\Migration;

class m150514_114054_alterReview extends Migration
{
    public function up()
    {
        $submissionObject = Object::getForClass(\app\models\Submission::className());
        /** @var PropertyHandler $propertyHandler */
        $propertyHandler = PropertyHandler::findOne(
            [
                'name'=>'Text'
            ]
        );
        $this->addColumn(Review::tableName(), 'submission_id', 'INT UNSIGNED NOT NULL');
        $form = new \app\models\Form;
        $form->name = 'Review form';
        $form->email_notification_addresses = '';
        $form->email_notification_view = '@app/modules/review/views/review-email-template.php';
        $form->save(false, ['name', 'email_notification_addresses', 'email_notification_view']);
        $propertyGroup = new PropertyGroup;
        $propertyGroup->attributes = [
            'object_id' => $form->object->id,
            'name' => 'Review form additional properties',
            'hidden_group_title' => 1,
        ];
        $propertyGroup->save(true, ['object_id', 'name', 'hidden_group_title']);
        $nameProperty = new Property;
        $nameProperty->attributes = [
            'property_group_id' => $propertyGroup->id,
            'name' => 'Name',
            'key' => 'name',
            'property_handler_id' => $propertyHandler->id,
            'handler_additional_params' => '{}',
            'is_eav' => 1,
        ];
        $nameProperty->save(true, ['property_group_id', 'name', 'key', 'property_handler_id', 'is_eav', 'handler_additional_params']);
        $phoneProperty = new Property;
        $phoneProperty->attributes = [
            'property_group_id' => $propertyGroup->id,
            'name' => 'Phone',
            'key' => 'phone',
            'property_handler_id' => $propertyHandler->id,
            'handler_additional_params' => '{}',
            'is_eav' => 1,
        ];
        $phoneProperty->save(true, ['property_group_id', 'name', 'key', 'property_handler_id', 'is_eav', 'handler_additional_params']);
        $objectPropertyGroup = new ObjectPropertyGroup;
        $objectPropertyGroup->attributes = [
            'object_id' => $form->object->id,
            'object_model_id' => $form->id,
            'property_group_id' => $propertyGroup->id,
        ];
        $objectPropertyGroup->save(true, ['object_id', 'object_model_id', 'property_group_id']);
        $reviews = Review::find()->all();
        foreach ($reviews as $review) {
            $submission = new \app\models\Submission;
            $submission->form_id = $form->id;
            $submission->processed_by_user_id = $review->author_user_id;
            $submission->date_received = $review->date_submitted;
            $submission->save(false, ['form_id', 'processed_by_user_id', 'date_received']);
            $review->submission_id = $this->db->lastInsertID;
            $review->save(true, ['submission_id']);
            $this->insert(
                ObjectPropertyGroup::tableName(),
                [
                    'object_id' => $submissionObject->id,
                    'object_model_id' => $submission->id,
                    'property_group_id' => $propertyGroup->id,
                ]
            );
            $this->insert(
                $submissionObject->eav_table_name,
                [
                    'object_model_id' => $submission->id,
                    'property_group_id' => $propertyGroup->id,
                    'key' => $nameProperty->key,
                    'value' => $review->author_name,
                ]
            );
            $this->insert(
                $submissionObject->eav_table_name,
                [
                    'object_model_id' => $submission->id,
                    'property_group_id' => $propertyGroup->id,
                    'key' => $phoneProperty->key,
                    'value' => $review->author_phone,
                ]
            );
        }
        $this->dropColumn(Review::tableName(), 'date_submitted');
        $this->dropColumn(Review::tableName(), 'author_user_id');
        $this->dropColumn(Review::tableName(), 'author_name');
        $this->dropColumn(Review::tableName(), 'author_phone');
        $this->dropColumn(Review::tableName(), 'rate');
        $this->renameColumn(Review::tableName(), 'text', 'review_text');
        $this->alterColumn(Review::tableName(), 'rating_id', 'CHAR(32)');
        $this->update(
            BackendMenu::tableName(),
            ['route' => 'review/backend-rating/index'],
            ['route' => 'backend/rating/index']
        );
        $this->update(
            BackendMenu::tableName(),
            ['route' => 'review/backend-review/index'],
            ['name' => 'Reviews']
        );
        $this->delete(BackendMenu::tableName(), ['route' => ['review/backend/products', 'review/backend/pages']]);
        $this->alterColumn(RatingValues::tableName(), 'rating_id', 'CHAR(32) NOT NULL');
        $this->alterColumn(RatingValues::tableName(), 'object_id', 'INT UNSIGNED NOT NULL');
        $this->alterColumn(RatingValues::tableName(), 'object_model_id', 'INT UNSIGNED NOT NULL');
        $this->alterColumn(RatingValues::tableName(), 'rating_item_id', 'INT UNSIGNED NOT NULL');
        $this->alterColumn(RatingValues::tableName(), 'user_id', 'INT UNSIGNED NOT NULL');
        $this->createIndex('ix-rating_values-rating_id', RatingValues::tableName(), 'rating_id');
        $this->createIndex(
            'ix-rating_values-object_id-object_model_id',
            RatingValues::tableName(),
            ['object_id', 'object_model_id']
        );
        $this->createIndex('ix-rating_item-rating_group', RatingItem::tableName(), 'rating_group');
    }

    public function down()
    {
        echo "You have no way back.\n";
        return false;
    }
}
