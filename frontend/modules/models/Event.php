<?php

namespace frontend\modules\models;

use Yii;

/**
 * This is the model class for table "events".
 *
 * @property integer $event_id
 * @property integer $user_id
 * @property integer $e_type_id
 * @property string $caption
 * @property string $description
 * @property string $start_time
 * @property string $start_date
 * @property string $end_time
 * @property string $end_date
 *
 * @property EventMeta[] $eventMetas
 * @property User $user
 * @property EventTypes $eType
 */
class Event extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'events';
    }



    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'caption', 'description', 'start_time', 'start_date', 'end_time', 'end_date'], 'required'],
            [['user_id', 'e_type_id'], 'integer'],
            [['start_time', 'start_date', 'end_time', 'end_date'], 'safe'],
            [['caption'], 'string', 'max' => 255],
            [['description'], 'string', 'max' => 2000],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
            [['e_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => EventTypes::className(), 'targetAttribute' => ['e_type_id' => 'e_type_id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'event_id' => 'Event ID',
            'user_id' => 'User ID',
            'e_type_id' => 'E Type ID',
            'caption' => 'Caption',
            'description' => 'Description',
            'start_time' => 'Start Time',
            'start_date' => 'Start Date',
            'end_time' => 'End Time',
            'end_date' => 'End Date',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEventMetas()
    {
        return $this->hasMany(EventMeta::className(), ['event_id' => 'event_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEType()
    {
        return $this->hasOne(EventTypes::className(), ['e_type_id' => 'e_type_id']);
    }
}
