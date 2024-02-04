<?php
require_once $_SERVER["DOCUMENT_ROOT"] . 'config.php';
class Captcha
{
    private $font = JSTORE_CAPTCHA_FONT_URL; // Путь к шрифту TTF
    private $imgWidth = 150;
    private $imgHeight = 50;

    /**
     * Generates a captcha image, stores the code in the session, and outputs the image.
     *
     * The method creates a 6-character code and displays it on a white background with random
     * text position and angle. The code is stored in the 'captcha_code' session variable and 
     * the generated image is output in PNG format. Make sure to call session_start() before
     * generating the captcha.
     */
    public function generate()
    {
        $code = substr(sha1(uniqid()), 0, 6);

        // Сохранение кода капчи в сессии
        $_SESSION['captcha_code'] = $code;

        // Создание изображения
        $img = imagecreatetruecolor($this->imgWidth, $this->imgHeight);

        $background = imagecolorallocate($img, 255, 255, 255); // Белый
        $textColor = imagecolorallocate($img, 0, 0, 0); // Черный
        imagefilledrectangle($img, 0, 0, $this->imgWidth, $this->imgHeight, $background);
        imagettftext($img, 24, rand(-20, 20), rand(10, 30), rand(30, 40), $textColor, $this->font, $code);

        // Вывод изображения
        header('Content-type: image/png');
        imagepng($img);

        imagedestroy($img);
    }
}