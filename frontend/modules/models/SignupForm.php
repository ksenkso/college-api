<?php
namespace frontend\modules\models;

use yii\base\Model;
use common\models\User;

/**
 * Signup form
 */
class SignupForm extends Model
{
    public $username;
    public $groupId;
    public $role;
    public $firstName;
    public $lastName;
    public $email;
    public $password;


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['username', 'trim'],
            ['username', 'required'],
            ['username', 'unique', 'targetClass' => '\common\models\User', 'message' => 'This username has already been taken.'],
            ['username', 'string', 'min' => 2, 'max' => 255],

            ['groupId', 'required'],
            ['groupId', 'integer'],

            ['firstName', 'trim'],
            ['firstName', 'required'],
            ['firstName', 'string', 'min' => 2, 'max' => 80],

            ['lastName', 'trim'],
            ['lastName', 'required'],
            ['lastName', 'string', 'min' => 2, 'max' => 80],

            ['email', 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            ['email', 'unique', 'targetClass' => '\common\models\User', 'message' => 'This email address has already been taken.'],

            ['password', 'required'],
            ['password', 'string', 'min' => 6],
        ];
    }

    /**
     * Signs user up.
     *
     * @return User|null the saved model or null if saving fails
     */
    public function signup()
    {
        if ($this->validate()) {

            $user = new User();
            $user->username = $this->username;
            $user->group_id = $this->groupId;
            $user->first_name = $this->firstName;
            $user->last_name = $this->lastName;
            $user->email = $this->email;
            $user->status = User::STATUS_NOT_CONFIRMED;
            $user->setPassword($this->password);
            $user->generateAuthKey();
            $user->save(false);

            $auth = \Yii::$app->authManager;
            if (!isset($this->role))
            {
                $this->role = 'student';
            }
            $studentRole = $auth->getRole($this->role);
            $auth->assign($studentRole, $user->getId());

            $notification = new Notification();
            $group = Group::find()->select(['abbrevation', 'year', 'group_number'])->where(['group_id' => $user->getId()])->asArray()->one();
            $groupName = $group['abbrevation'] . ' ' . $group['year'] . '.' . $group['group_count'];

            $type = Notification::NF_NEW_USER;
            $target = Notification::NF_TARGET_STEWARDS;
            $heading = Notification::NF_HEADING_USER_ADDED;
            $content = "Пользователь $user->last_name $user->first_name добавлен в группу $groupName";
            switch ($this->role)
            {
                case 'admin':
                    $heading = Notification::NF_HEADING_ADMIN_ADDED;
                    $content = "Администратор $user->last_name $user->first_name добавлен в группу $groupName";
                    $target = Notification::NF_TARGET_ADMINS;
                    $type = Notification::NF_NEW_ADMIN;
                    break;
                case 'steward':
                    $heading = Notification::NF_HEADING_STEWARD_ADDED;
                    $content = "Староста $user->last_name $user->first_name добавлен в группу $groupName";
                    $target = Notification::NF_TARGET_ADMINS;
                    $type = Notification::NF_NEW_STEWARD;
                    break;
                default: 
                    break;
            }

            $notification->target = $target;
            $notification->type = $type;
            $notification->from = $user->getId();
            $notification->heading = $heading;
            $notification->content = $content;
            $notification->save();
            \Yii::trace(json_encode($notification->getErrors()));

            return $user;
        }
        \Yii::trace(json_encode($this->getErrors()));
        
        return  null;
    }
}
