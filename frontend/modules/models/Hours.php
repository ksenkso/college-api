<?php

namespace frontend\modules\models;

use Yii;

/**
 * This is the model class for table "hours".
 *
 * @property integer $hours_id
 * @property string $date
 * @property integer $student_id
 * @property string $hours
 * @property integer $group_id
 * @property integer $hours_good
 * @property integer $is_good
 *
 * @property Students $student
 * @property Group $group
 */
class Hours extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tmp_hours';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['date', 'student_id', 'hours', 'group_id'], 'required'],
            [['date'], 'safe'],
            [['student_id', 'group_id', 'hours_good', 'is_good'], 'integer'],
            [['hours'], 'string', 'max' => 4],
            [['student_id'], 'exist', 'skipOnError' => true, 'targetClass' => Students::className(), 'targetAttribute' => ['student_id' => 'student_id']],
            [['group_id'], 'exist', 'skipOnError' => true, 'targetClass' => Group::className(), 'targetAttribute' => ['group_id' => 'group_id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'hours_id' => 'Hours ID',
            'date' => 'Date',
            'student_id' => 'Student ID',
            'hours' => 'Hours',
            'group_id' => 'Group ID',
            'hours_good' => 'Hours Good',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStudent()
    {
        return $this->hasOne(Students::className(), ['student_id' => 'student_id']);
    }

    public function getStudentFullName()
    {
        return $this->student ? $this->student->last_name . ' ' . $this->student->first_name : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGroup()
    {
        return $this->hasOne(Group::className(), ['group_id' => 'group_id']);
    }

    public function getGroupName()
    {
        return $this->group
            ? $this->group->abbrevation . ' '
            . $this->group->year . '.'
            . $this->group->group_number
            : '';
    }
}
