<?php

namespace frontend\modules\models;

use Yii;

/**
 * This is the model class for table "today".
 *
 * @property integer $id
 * @property integer $student_id
 * @property string $hours
 * @property integer $group_id
 * @property string $
 * @property integer $is_good
 *
 * @property Students $student
 */
class Today extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'today';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['student_id', 'hours'], 'required'],
            [['student_id', 'is_good'], 'integer'],
            ['group_id', 'integer'],
            [['hours', 'hours_good'], 'string', 'max' => 4],
            [['student_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['student_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'student_id' => 'Student ID',
            'hours' => 'Hours',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStudent()
    {
        return $this->hasOne(User::className(), ['id' => 'student_id']);
    }

    /**
     * @inheritdoc
     * @return TodayQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new TodayQuery(get_called_class());
    }
}
