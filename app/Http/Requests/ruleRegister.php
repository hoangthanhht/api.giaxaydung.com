<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ruleRegister extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    // hàm này được ghi đè để lấy ra thông báo lỗi
    public $validator = null;
    protected function failedValidation($validator)
    {
        $this->validator = $validator;
    }

    public function rules()
    {
        return [
            'name' => 'bail|required|min:4',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            
        ];
    }
    // định nghĩa lại 1 trường với tên khác vd 'name'=>'newname'
    public function attributes()
    {
        return [
           
            
        ];
    }
    // hiện thông báo khi 1 validate nào đó không đáp ứng
    public function messages()
    {
        return [
            'name.required' => 'Tên không được bỏ trống',
            'password.required' => 'Password không được bỏ trống',
            'password.min' => 'Password ít nhất 6 ký tự',
            'email.unique' => 'Email đã tồn tại',// khi unique không thỏa mãn thì báo ta thông bào này
            'email.required' => 'Email không được bỏ trống',
            'email.email' => 'Email không đúng định dạng',
           
        ];
    }


    public function withValidator($validator)
    {
        // $validator->after(function ($validator) {
        //     $validator->errors()->add('field', 'Something is wrong with this field!');
        // });
    }
}
