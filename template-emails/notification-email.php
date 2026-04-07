<!DOCTYPE html>
<html lang="uk">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($display_title) ?></title>
</head>

<body style="margin: 0; padding: 60px 0; background-color: #F5F5F7;">
    <div class="email-container"
        style="margin: 60px auto; align-content: center; width: 717px; padding: 56px 10px; border-radius: 20px; background: #FFF; text-align: center;">
        <div class="email-header"
            style="margin-bottom: 42px; display: flex; flex-direction: column; gap: 12px; align-items: center;">
            <svg xmlns="http://www.w3.org/2000/svg" width="61" height="60" viewBox="0 0 61 60" fill="none">
                <path fill-rule="evenodd" clip-rule="evenodd"
                    d="M17.5687 11.004C22.4012 7.71435 28.3057 6.39013 34.0801 7.30088C36.4976 7.68216 38.8161 8.44315 40.958 9.53789C40.5683 10.2974 40.3484 11.1584 40.3484 12.0707C40.3484 15.1389 42.8357 17.6262 45.9039 17.6262C48.9722 17.6262 51.4595 15.1389 51.4595 12.0707C51.4595 9.00245 48.9722 6.51515 45.9039 6.51515C44.4888 6.51515 43.1972 7.04428 42.2162 7.91548C39.7925 6.6296 37.1528 5.74033 34.3949 5.30535C28.1128 4.31453 21.6892 5.75517 16.4319 9.33397C11.1747 12.9128 7.47896 18.3607 6.09723 24.5685C4.7155 30.7764 5.75162 37.2775 8.99465 42.7482C12.2377 48.219 17.4438 52.2482 23.5531 54.0154L24.1145 52.0748C18.4988 50.4503 13.7134 46.7468 10.7325 41.7181C7.75149 36.6894 6.7991 30.7136 8.06917 25.0074C9.33924 19.3012 12.7363 14.2936 17.5687 11.004ZM50.5677 15.0909C50.192 15.67 49.7113 16.1747 49.1526 16.578C52.3925 21.0803 53.9002 26.6124 53.3786 32.1537C52.8307 37.9738 50.0853 43.3663 45.7013 47.2334C41.8147 50.6618 36.8992 52.6626 31.7625 52.9451V48.2914L38.5397 44.786C38.8751 44.6125 39.0857 44.2664 39.0857 43.8888V35.303V28.2323V21.1616V17.6262H37.0655V20.5997L31.7625 23.8912V15.606H29.7423V23.8912L24.4393 20.5997V17.6262H22.4191V21.1616V28.2323V35.303V43.8888C22.4191 44.2664 22.6297 44.6125 22.9651 44.786L29.7423 48.2914V54.215L29.7181 54.9878C36.0748 55.1867 42.2683 52.9555 47.0377 48.7484C51.8071 44.5414 54.7939 38.6748 55.3899 32.343C55.9693 26.188 54.2472 20.0433 50.5677 15.0909ZM24.4393 22.9774L29.7423 26.2689V30.9619L24.4393 27.6704V22.9774ZM24.4393 30.0481V34.7242L29.7423 37.8329V33.3396L24.4393 30.0481ZM29.7423 40.1746L24.4393 37.066V43.2741L29.7423 46.017V40.1746ZM31.7625 46.017L37.0655 43.2741V37.066L31.7625 40.1746V46.017ZM31.7625 37.8329L37.0655 34.7242V30.0481L31.7625 33.3396V37.8329ZM31.7625 30.9619V26.2689L37.0655 22.9774V27.6704L31.7625 30.9619ZM49.4393 12.0707C49.4393 14.0232 47.8564 15.606 45.9039 15.606C43.9514 15.606 42.3686 14.0232 42.3686 12.0707C42.3686 10.1182 43.9514 8.53535 45.9039 8.53535C47.8564 8.53535 49.4393 10.1182 49.4393 12.0707Z"
                    fill="#147575" />
            </svg>
        </div>
        <div class="email-content">
            <h1 style="color: #272727; font-size: 18px; font-weight: 700; line-height: 28px; margin-bottom: 4px;">
                <?= htmlspecialchars($display_title) ?></h1>
            <p
                style="color: #637381; font-size: 16px; font-weight: 400; line-height: 24px; margin: 0 auto; max-width: 597px">
                <?= __('Вітаємо ', 'panterrea_v1') ?> <?= htmlspecialchars($display_name) ?>
            </p>
            <p
                style="color: #637381; font-size: 16px; font-weight: 400; line-height: 24px; margin: 0 auto 42px; max-width: 597px">
                <?= htmlspecialchars($display_message) ?></p>

            <p
                style="color: #637381; font-size: 16px; font-weight: 400; line-height: 24px; margin: 24px auto 0; max-width: 597px">
                Переходьте на сайт <a style="text-decoration: none; color: #147575; font-weight: 700;"
                    href="https://panterrea.com/">PanTerrea</a>
            </p>
        </div>
    </div>
</body>

</html>