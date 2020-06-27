<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;


class CustomerService
{

    /**
     * Handle file Uploaded 
     * @param UploaedFile $file
     * @author ThamNT
     */
    public function handleUploadedFile($file)
    {
        try {
            $type = exif_imagetype($file) == 3 ? 'png' : 'jpg';
            $current = date('YmdHis');
            $fileName = $current . '_' . $file->getClientOriginalName();
            Storage::disk('s3')->putFileAs(config('constant.folder_avatar_customer_s3') . '/' . $type, $file,  $fileName, 'public');
            return $type . '/' . $fileName;
        } catch (\Exception $e) {
            throw $e;
        }
    }



    /**
     * Get customer information from Pay.jp by $token that gotten when registed card
     * @doc: https://github.com/payjp/payjp-php/blob/master/RequestExample.md
     * 
     * @param $token
     * @author HoangNN
     */
    public function getCustomerInfoPayJP($token)
    {
        try {
            $payjp_private_key = env('PAYJP_PRIVATE_KEY');
            \Payjp\Payjp::setApiKey($payjp_private_key);

            $customer = \Payjp\Customer::create([//顧客登録
                'card' => $token,
                'description' => "Trust customer by Token: $token"
              ]);

            return $customer;
        } catch (\Exception $e) {
            throw $e;
        }
    }


    /**
     * Get customer information from Pay.jp by $token that gotten when registed card
     * @doc: https://github.com/payjp/payjp-php/blob/master/RequestExample.md
     * 
     * @param $token
     * @author HoangNN
     */
    public function getTokenPayJP($params)
    {
        try {
            $payjp_private_key = env('PAYJP_PRIVATE_KEY');
            $payjp_public_key = env('PAYJP_PUBLIC_KEY'); 
            \Payjp\Payjp::setApiKey($payjp_public_key);

            $token = \Payjp\Token::create($params, $options = ['payjp_direct_token_generate' => 'true']);

            return $token;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
