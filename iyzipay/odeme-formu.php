<?php

require '../config.php';
require_once('./samples/config.php');

$token = $_GET["token"];
$id = $_GET["id"];


if (empty($id) || empty($token)) {
    echo json_encode(['status' => 'error']);
    die();
}
$user = getUser($econfig, $token);
$urun = (object) getUrun($econfig, $token, $id);
$diger = (object) getDiger($token, $id, $urun->adresDurum);


function getUser($econfig, $token)
{
    //GET USER DATA
    $url = $econfig->base_url . 'api/profile';
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt($ch, CURLOPT_POST, 1);
    $headers = array(
        'Content-type: application/xml',
        'token: ' . $token,

    );
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch); //kullanıcılar

    $user =  json_decode($response)->data;

    if (empty($user)) {
        die();
    }
    curl_close($ch);
    return $user;
}

function getUrun($econfig, $token, $id)
{
    //GET URUN BİLGİLERİ
    $url =  $econfig->base_url . '/api/tables/market/' . $id . '/get';
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt($ch, CURLOPT_POST, 1);
    $headers = array(
        'Content-type: application/xml',
        'token: ' . $token,

    );
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch); //kullanıcılar
    $urun =  json_decode($response)->data;
    if (empty($urun)) {
        die();
    }
    curl_close($ch);
    return $urun;
}

function getDiger($token, $id, $adresDurum)
{
    if ($adresDurum == '1' && (empty($_GET['tc']) || empty($_GET['adres']) || empty($_GET['sehir']) || empty($_GET['ulke']) || empty($_GET['zipcode']))) {
?>
        <!-- CSS only -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-Zenh87qX5JnK2Jl0vWa8Ck2rdkQ2Bzep5IDxbcnCeuOxjzrPF/et3URy9Bv1WTRi" crossorigin="anonymous">
        <form name="form" action="" method="get">
            <label for=""> TC No:</label>
            <input type="number" class="form-control" name="tc" require value="<?php echo $_GET['tc'] ?? '' ?>">

            <label for="">Detaylı Adres:</label>
            <textarea type="text" class="form-control" name="adres" require value="<?php echo $_GET['adres'] ?? '' ?>"></textarea>

            <label for="">Zip Kodu:</label>
            <input type="text" class="form-control" name="zipcode" require value="<?php echo $_GET['zipcode'] ?? '' ?>">


            <label for="">Şehir:</label>
            <input type="text" class="form-control" name="sehir" require value="<?php echo $_GET['sehir'] ?? '' ?>">

            <label for="">Ülke:</label>
            <input type="text" class="form-control" name="ulke" require value="<?php echo $_GET['ulke'] ?? '' ?>">

            <label for="">Ülke:</label>
            <input type="text" class="form-control" name="ulke" require value="<?php echo $_GET['ulke'] ?? '' ?>">

            <input type="hidden" name="token" value="<?php echo $token ?>">
            <input type="hidden" name="id" value="<?php echo $id ?>">
        </form>
<?php
    }
    return $_GET;
}
die();
# create request class
$request = new \Iyzipay\Request\CreateCheckoutFormInitializeRequest();
$request->setLocale(\Iyzipay\Model\Locale::TR);
$request->setConversationId($urun->id); //Benzersiz oluşturulması gereken ürün kodu
$request->setPrice($urun->product_amount); // Ürün fiyatı 
$request->setPaidPrice($urun->product_amount);
$request->setCurrency(\Iyzipay\Model\Currency::TL);
$request->setBasketId($urun->id);
$request->setPaymentGroup(\Iyzipay\Model\PaymentGroup::PRODUCT);
$request->setCallbackUrl($econfig->base_url . "/iyzipay/odeme-basarili.php");
$request->setEnabledInstallments(array(2, 3, 6, 9));

$buyer = new \Iyzipay\Model\Buyer();
$buyer->setId($user->id);
$buyer->setName($user->name);
$buyer->setSurname($user->surname);
$buyer->setGsmNumber($user->phone);
$buyer->setEmail($user->email);
$buyer->setIdentityNumber($diger->tc ?? '00000000000');
$buyer->setLastLoginDate(date('Y-m-d H:i:s'));
$buyer->setRegistrationDate(date('Y-m-d H:i:s'));
$buyer->setRegistrationAddress($diger->adres);
$buyer->setIp($this->input->ip_address());
$buyer->setCity($diger->sehir);
$buyer->setCountry($diger->ulke);
$buyer->setZipCode($diger->zipcode);
$request->setBuyer($buyer);

$shippingAddress = new \Iyzipay\Model\Address();
$shippingAddress->setContactName("Jane Doe");
$shippingAddress->setCity("Istanbul");
$shippingAddress->setCountry("Turkey");
$shippingAddress->setAddress("Nidakule Göztepe, Merdivenköy Mah. Bora Sok. No:1");
$shippingAddress->setZipCode("34742");
$request->setShippingAddress($shippingAddress);

$billingAddress = new \Iyzipay\Model\Address();
$billingAddress->setContactName("Jane Doe");
$billingAddress->setCity("Istanbul");
$billingAddress->setCountry("Turkey");
$billingAddress->setAddress("Nidakule Göztepe, Merdivenköy Mah. Bora Sok. No:1");
$billingAddress->setZipCode("34742");
$request->setBillingAddress($billingAddress);

$basketItems = array();
$firstBasketItem = new \Iyzipay\Model\BasketItem();
$firstBasketItem->setId($urun->id);
$firstBasketItem->setName($urun->product_name);
$firstBasketItem->setCategory1("Kategori 1");
$firstBasketItem->setCategory2("Kategori 2");
$firstBasketItem->setItemType(\Iyzipay\Model\BasketItemType::PHYSICAL);
$firstBasketItem->setPrice($urun->product_amount);
$basketItems[0] = $firstBasketItem;
$request->setBasketItems($basketItems);

# make request
$checkoutFormInitialize = \Iyzipay\Model\CheckoutFormInitialize::create($request, Config::options());
print_r($checkoutFormInitialize->getCheckoutFormContent());


//SET İŞLEM BİLGİLERİ
$url =  $econfig->base_url . '/api/tables/market_islemler/store';
$ch = curl_init($url);

curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'token' => $checkoutFormInitialize->getToken(),
    'product_code' => $urun->id,
    'user' => $user->id,
    'state' => $checkoutFormInitialize->getStatus()
]));
curl_setopt($ch, CURLOPT_POST, 1);
$headers = array(
    'Content-type: application/xml',
    'token: ' . $token,

);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch); //kullanıcılar

curl_close($ch);
?>

<div id="iyzipay-checkout-form" class="responsive"></div>