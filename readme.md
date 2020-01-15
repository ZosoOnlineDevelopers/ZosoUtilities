# Zoso Utility

#### Logging configuration toevoegen in je app(.default).php
    'mail' => [
        'className' => 'Cake\Log\Engine\FileLog',
        'path' => LOGS,
        'file' => 'mail',
        'levels' => ['info'],
        'scopes' => ['mail'],
    ]
    
#### Utility in je code toevoegen
    use App\Utility\Email;

#### Utility gebruiken (Postmark)
    $email = new Email();
    $email->setClient('Postmark')
        ->setApiKey('API_KEY')
        ->setSubject($subject)
        ->setFrom($from)
        ->setFromName($fromName)
        ->setTo($to)
        ->setToName($toName)
        ->setReplyTo($replyTo)
        ->setTemplate($templateIdOrAlias)
        ->setTemplateVars([
            'websiteurl' => 'https://www.zoso.nl',
            'logo' => 'https://www.zoso.nl/img/logo-email.png',
            'title' => 'Titel!',
            'body' => '<p>Dit is de <b>body</b>, zeer belangrijke content vind je <a href="#">hier<a>.</p>',
            'footer_text' => 'Dit is een automatisch gegenereerd bericht. Je kunt hier niet op reageren. Heb je vragen over deze e-mail? Kijk dan op zoso.nl'
        ])
        ->setAttachments([
            'paintball-volvo.jpeg' => WWW_ROOT . 'img' . DS . 'paintball-volvo.jpeg',
            'paintball-terrein1.jpeg' => WWW_ROOT . 'img' . DS . 'paintball-terrein1.jpeg'
        ]);
        
    $emailResult = $email->sendMail();

#### Utility gebruiken (Mandrill)
    $email = new Email();
    $email->setClient('Mandrill')
        ->setApiKey('API_KEY')
        ->setSubject($subject)
        ->setFrom($from)
        ->setFromName($fromName)
        ->setTo($to)
        ->setToName($toName)
        ->setReplyTo($replyTo)
        ->setTemplate($templateName)
        ->setTemplateVars([
            'header_logo' => '<a href="#"><img src="https://example.com/img/logo-email.png" alt="Zoso" /></a>',
            'header_text' => '<a href="#" class="websitelink" >'.__('Ga naar de website').' &raquo;</a>',
            'titel' => 'Dit is de titel!',
            'bericht' => '<p>We testen even dit test bericht. <br>Byebye</p>',
            'footer_text' => '<small>'.__('Dit is een automatisch gegenereerd bericht. U kunt hier niet op reageren. Heeft u vragen over deze e-mail? Kijk dan op onze website').'</small>'
        ])
        ->setSubaccount($subaccount)
        ->setAttachments([
            'paintball-volvo.jpeg' => WWW_ROOT . 'img' . DS . 'paintball-volvo.jpeg',
            'paintball-terrein1.jpeg' => WWW_ROOT . 'img' . DS . 'paintball-terrein1.jpeg'
        ]);
        
    $emailResult = $email->sendMail();

[![Build Status](https://github.com/ZosoOnlineDevelopers/ZosoUtilities)](https://github.com/ZosoOnlineDevelopers/ZosoUtilities)