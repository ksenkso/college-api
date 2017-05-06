<?php

namespace frontend\modules\models;

use Yii;

/**
 * This is the model class for table "event_types".
 *
 * @property integer $e_type_id
 * @property string $type
 *
 * @property Events[] $events
 */
class EventTypes extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'event_types';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type'], 'string', 'max' => 20],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'e_type_id' => 'E Type ID',
            'type' => 'Type',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEvents()
    {
        return $this->hasMany(Events::className(), ['e_type_id' => 'e_type_id']);
    }
}
