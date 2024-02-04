<?php
require_once $_SERVER["DOCUMENT_ROOT"] . 'config.php';

/**
 * Captcha - A system for generating and validating captchas.
 *
 * This class offers methods to create a visual or numeric captcha challenge and verify
 * the user's response. It's an effective tool against automated attacks by ensuring that
 * the respondent is a human. The captcha generation involves creating images with
 * numbers or text that humans can read but are challenging for bots.
 *
 * PHP version 7.4
 * @category   Security
 * @package    solenox-crm
 * @subpackage CaptchaVerification
 * @author     Vasiliy Kravchuk <hellendedman@internet.ru>
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @link       http://j2me.xyz
 * @since      Class available since RC 1.1.18
 */
class Captcha
{

    /**
     * Generates a numeric captcha image and stores the captcha value in the session.
     *
     * This method creates a numeric captcha string, stores it in the PHP session under the key 'captcha',
     * and outputs a JPEG image with the captcha text rendered onto it.
     *
     * The captcha consists of a 5-digit random number. The generated image has a plain white
     * background with the captcha text in black.
     *
     * Note: It is important to start a session with `session_start()` before calling this method to
     * ensure that the captcha value can be stored and later accessed for verification.
     */
    public function generateCaptcha()
    {
        $text = rand(10000, 99999);

        $height = 40; // Высота изображения капчи
        $width = 100; // Ширина изображения капчи

        // Создаем пустое изображение
        $image = imagecreate($width, $height);

        // Задаем цвета: белый для фона, черный для текста и серый для шума
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);
        $grey = imagecolorallocate($image, 204, 204, 204);

        // Заполнение фона
        imagefill($image, 0, 0, $white);

        // Добавление случайных точек для шума
        for ($i = 0; $i < 1000; $i++) {
            imagesetpixel($image, rand(0, $width), rand(0, $height), $grey);
        }

        // Добавление текста капчи
        $font = JSTORE_CAPTCHA_FONT_URL; // Укажите путь к подходящему TTF-шрифту
        $fontSize = 14; // Размер шрифта
        imagettftext($image, $fontSize, 0, 11, 21, $black, $font, $text);

        // Отправка изображения в браузер
        header('Content-type: image/jpeg');
        imagejpeg($image);
        imagedestroy($image);
        $_SESSION["captcha"] = '';
        $_SESSION["captcha"] = $text;

    }

    /**
     * Verifies if the provided input from the user matches the stored captcha.
     *
     * This method checks if the value input by the user is the same as the value of the captcha
     * stored in the session. It is case-sensitive and checks for an exact match.
     *
     * @param string $userCaptcha The captcha value entered by the user to be verified.
     * @return bool Returns true if the input matches the stored captcha, otherwise false.
     */
    public function checkCaptcha($userCaptcha)
    {
        if ($_SESSION['captcha'] !== $userCaptcha) {
            return false;
        }
        return true;
    }
}
