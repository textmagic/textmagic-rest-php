<?php

require __DIR__ . "\Services\TextmagicRestClient.php";

use Textmagic\Services\TextmagicRestClient;
use Textmagic\Services\RestException;

define('VERSION', '0.01');

/**
 * Client object
 */
$client = new TextmagicRestClient('<USERNAME>', '<APIV2_TOKEN>');

/**
 * User object
 */
$user = false;

/**
 * Pagination
 */
$page  = 1;
$limit = 10;
$paginatedFunction = 'exitOk';

/**
 * sendMessage containers
 */
$sendingContacts = array();
$sendingLists    = array();

/**
 * Default "Back to main menu" link
 */
$backMenu = array(
    'Back to main menu' => 'showMainMenu'
);

/**
 *  Show main menu
 */
function showMainMenu() {
    flushPagination();
    
    $items = array(
        'Contacts'  => 'showAllContacts',
        'Lists' => 'showAllLists',
        'Messages' => 'showMessagesMenu',
        'Templates' => 'showAllTemplates',
        'Information' => 'showInformation'
    );
    
    showMenu($items);
}

/**
 *  Show messages menu
 */
function showMessagesMenu() {
    global $backMenu;
    
    $items = array(
        'Show outgoing messages'  => 'showMessagesOut',
        'Show incoming messages'  => 'showMessagesIn',
        'Send message'  => 'sendMessage'
    );
    
    showMenu($items + $backMenu);
}

/**
 *  Show base account information
 */
function showInformation() {
    global $user, $backMenu;
    
    print <<<EOT

ACCOUNT INFORMATION
===================

ID          : {$user['id']}
Username    : {$user['username']}
First Name  : {$user['firstName']}
Last Name   : {$user['lastName']}
Balance     : {$user['balance']} {$user['currency']['id']}
Timezone    : {$user['timezone']['timezone']} ({$user['timezone']['offset']})

EOT;

    showMenu($backMenu);
}

/**
 *  Show all user contacts (including shared)
 */
function showAllContacts() {
    global $client, $page, $limit, $paginatedFunction, $backMenu;
    
    $paginatedFunction = 'showAllContacts';
    
    try {
        $response = $client->contacts->getList(
            array(
                'page' => $page,
                'limit' => $limit,
                'shared' => true
            )
        );
    } catch (\ErrorException $e) {
        error($e);
    }
    
    $contacts = $response['resources'];
    
    print <<<EOT

ALL CONTACTS
============
Page {$response['page']} of {$response['pageCount']}

EOT;

    foreach ($contacts as $contact) {
        print "{$contact['id']}. {$contact['firstName']} {$contact['lastName']}, {$contact['phone']}\n";
    }
        
    $items = array(
        'Previous page' => 'goToPreviousPage',
        'Next page' => 'goToNextPage',
        'Show contact details' => 'showContact',
        'Delete contact' => 'deleteContact'
    );
    
    showMenu($items + $backMenu);
}

/**
 *  Show one contact details
 */
function showContact() {
    global $client;
    
    $id = readNumber("Enter contact ID");
    
    if (!$id) {
        return showAllContacts();
    }
    
    try {
        $contact = $client->contacts->get($id);
    } catch (\ErrorException $e) {
        error($e);
    }
    
    print <<<EOT
    
CONTACT INFORMATION
===================

Name    : {$contact['firstName']} {$contact['lastName']}
Phone   : +{$contact['phone']} ({$contact['country']['name']})
Company : {$contact['companyName']}

EOT;
    
    return showAllContacts();
}

/**
 *  Delete contact permanently
 */
function deleteContact() {
    global $client;
    
    $id = readNumber("Enter contact ID");
    
    if (!$id) {
        return showAllContacts();
    }
    
    try {
        $client->contacts->delete($id);
    } catch (\ErrorException $e) {
        error($e);
    }
    
    print "\nContact deleted successfully\n";
    return showAllContacts();
}

/**
 *  Show all user lists (including shared)
 */
function showAllLists() {
    global $client, $page, $limit, $paginatedFunction, $backMenu;
    
    $paginatedFunction = 'showAllLists';
    
    try {
        $response = $client->lists->getList(
            array(
                'page' => $page,
                'limit' => $limit,
                'shared'  => true
            )
        );
    } catch (\ErrorException $e) {
        error($e);
    }
    
    $lists = $response['resources'];
    
    print <<<EOT

ALL LISTS
=========
Page {$response['page']} of {$response['pageCount']}

EOT;

    foreach ($lists as $list) {
        print "{$list['id']}. {$list['name']} ({$list['description']})\n";
    }
        
    $items = array(
        'Previous page' => 'goToPreviousPage',
        'Next page' => 'goToNextPage'
    );
    
    showMenu($items + $backMenu);
}

/**
 *  Show all sent messages
 */
function showMessagesOut() {
    global $client, $page, $limit, $paginatedFunction, $backMenu;
    
    $paginatedFunction = 'showMessagesOut';
    
    try {
        $response = $client->messages->getList(
            array(
                'page' => $page,
                'limit' => $limit
            )
        );
    } catch (\ErrorException $e) {
        error($e);
    }
    
    $messages = $response['resources'];
    
    print <<<EOT

SENT MESSAGES
=========
Page {$response['page']} of {$response['pageCount']}

EOT;

    foreach ($messages as $message) {
        print "{$message['id']}. {$message['text']} (from {$message['receiver']})\n";
    }
        
    $items = array(
        'Previous page' => 'goToPreviousPage',
        'Next page' => 'goToNextPage',
        'Delete message' => 'deleteMessageOut'
    );
    
    showMenu($items + $backMenu);
}

/**
 *  Delete one sent message
 */
function deleteMessageOut() {
    global $client;
    
    $id = readNumber("Enter message ID");
    
    if (!$id) {
        return showMessagesOut();
    }
    
    try {
        $client->messages->delete($id);
    } catch (\ErrorException $e) {
        error($e);
    }
    
    print "\nMessage deleted successfully\n";
    return showMessagesOut();
}

/**
 *  Show all received messages
 */
function showMessagesIn() {
    global $client, $page, $limit, $paginatedFunction, $backMenu;
    
    $paginatedFunction = 'showMessagesIn';
    
    try {
        $response = $client->replies->getList(
            array(
                'page' => $page,
                'limit' => $limit
            )
        );
    } catch (\ErrorException $e) {
        error($e);
    }
    
    $replies = $response['resources'];
    
    print <<<EOT

RECEIVED MESSAGES
=========
Page {$response['page']} of {$response['pageCount']}

EOT;

    foreach ($replies as $message) {
        print "{$message['id']}. {$message['text']} (from {$message['sender']})\n";
    }
        
    $items = array(
        'Previous page' => 'goToPreviousPage',
        'Next page' => 'goToNextPage',
        'Delete message' => 'deleteMessageIn'
    );
    
    showMenu($items + $backMenu);
}

/**
 *  Delete one received message
 */
function deleteMessageIn() {
    global $client;
    
    $id = readNumber("Enter message ID");
    
    if (!$id) {
        return showMessagesIn();
    }
    
    try {
        $client->replies->delete($id);
    } catch (\ErrorException $e) {
        error($e);
    }
    
    print "\nMessage deleted successfully\n";
    return showMessagesIn();
}

/**
 *  Show all message templates
 */
function showAllTemplates() {
    global $client, $page, $limit, $paginatedFunction, $backMenu;
    
    $paginatedFunction = 'showAllTemplates';
    
    try {
        $response = $client->templates->getList(
            array(
                'page' => $page,
                'limit' => $limit
            )
        );
    } catch (\ErrorException $e) {
        error($e);
    }
    
    $templates = $response['resources'];
    
    print <<<EOT

TEMPLATES
=========
Page {$response['page']} of {$response['pageCount']}

EOT;

    foreach ($templates as $template) {
        print "{$template['id']}. {$template['name']}: {$template['content']}\n";
    }
        
    $items = array(
        'Previous page' => 'goToPreviousPage',
        'Next page' => 'goToNextPage',
        'Delete template' => 'deleteTemplate'
    );
    
    showMenu($items + $backMenu);
}

/**
 *  Delete one message template
 */
function deleteTemplate() {
    global $client;
    
    $id = readNumber("Enter template ID");
    
    if (!$id) {
        return showAllTemplates();
    }
    
    try {
        $client->templates->delete($id);
    } catch (\ErrorException $e) {
        error($e);
    }
    
    print "\nTemplate deleted successfully\n";
    return showAllTemplates();
}

/**
 *  Send outgoing message to phones, contacts and/or contact lists
 */
function sendMessage() {
    global $client;
    
    print <<<EOT

SEND MESSAGE
============

EOT;
    print "Text: ";
    $sendingText = trim(fgets(STDIN));
    print "\n\n";

    print "Enter phone numbers, separated by [ENTER]. Empty string to break.\n";

    $sendingPhones = array();
    $sendingContacts = array();
    $sendingLists = array();

    do {
       print "\nPhone: ";
       $phone = trim(fgets(STDIN));
       array_push($sendingPhones, $phone);
    } while ($phone);
    array_pop($sendingPhones);

    print "\n\nEnter contact IDs, separated by [ENTER]. Empty string to break.\n";

    do {
       $contact = readNumber('Contact');
       array_push($sendingContacts, $contact);
    } while ($contact);
    array_pop($sendingContacts);
    
    print "\n\nEnter list IDs, separated by [ENTER]. Empty string to break.\n";
    
    do {
       $list = readNumber('List');
       array_push($sendingLists, $list);
    } while ($list);
    array_pop($sendingLists);
    
    $sendingPhones = implode(', ', $sendingPhones);
    $sendingContacts = implode(', ', $sendingContacts);
    $sendingLists = implode(', ', $sendingLists);
    
    print "\n\nYOU ARE ABOUT TO SEND MESSAGES TO:" .
          ($sendingPhones ? "\nPhone numbers: " . $sendingPhones : '') .
          ($sendingContacts ? "\nContacts: "  . $sendingContacts : '') .
          ($sendingLists ? "\nLists: " . $sendingLists : '');
    print "\nAre you sure (y/n)? ";
    
    $answer = strtolower(trim(fgets(STDIN)));
    if ($answer != 'y') {
        return showMainMenu();
    }
    
    try {
        $result = $client->messages->create(
            array(
                'text' => $sendingText,
                'phones' => $sendingPhones,
                'contacts' => $sendingContacts,
                'lists' => $sendingLists
            )
        );
    } catch (\ErrorException $e) {
        error($e);
    }

    print "\nMessage {$result['id']} sent\n";
    
    return showMainMenu();
}

/**
 *  Error handler
 */
function error($e) {
    if ($e instanceof RestException) {
        print '[ERROR] ' . $e->getMessage() . "\n";
        foreach ($e->getErrors() as $key => $value) {
            print '[' . $key . '] ' . $value . "\n";
        }
    } else {
        print '[ERROR] ' . $e->getMessage() . "\n";
    }
    
    exit(0);
}

/**
 *  Show top user banner
 */
function showUserInfo() {
    global $user;
    
    print 'TextMagic CLI v' . VERSION . " || {$user['firstName']}  {$user['lastName']} ({$user['username']}) || {$user['balance']} {$user['currency']['id']}\n";
}

/**
 *  Show numered menu and return user choice
 */
function showMenu($itemsRef) {
    $functionsRef = array();
    print "\n";
    
    $i = 0;
    foreach ($itemsRef as $key => $value) {
        $i++;
        print $i . ' ' . $key ."\n";
        $functionsRef[$i] = $value;
    }
    
    $i++;
    print $i . " Exit\n";
    $functionsRef[$i] = 'exitOk';
    
    $choice = readNumber("Your choice ($i)");

    if (!$choice || !isset($functionsRef[$choice])) {
        $function = $functionsRef[$i];
    } else {
        $function = $functionsRef[$choice];
    }
    
    $function();
}

/**
 *  Go to previous page when browsing paginated resource
 */
function goToPreviousPage() {
    global $page, $paginatedFunction;
    
    if ($page <= 2) {
        $page = 1;
    } else {
        $page--;
    }
    
    $paginatedFunction();
}

/**
 *  Go to next page when browsing paginated resource
 */
function goToNextPage() {
    global $page, $paginatedFunction;
    
    $page++;
    
    $paginatedFunction();
}

/**
 *  Reset current page, limit and paginated resource fetch function 
 */
function flushPagination() {
    global $page, $limit, $paginatedFunction;
    
    $page = 1;
    $limit = 10;
    $paginatedFunction = 'exitOk';
}

/**
 *  Normal program termination
 */
function exitOk() {
    print "\nBye!\n";
    exit(0);
}

/**
 *  Read number value
 */
function readNumber($text) {
    print "\n$text: ";
    $choice = intval(trim(fgets(STDIN)));
    
    return $choice;
}

/**
 *  Main program procedure
 */
function main() {
    global $client, $user;
    
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
        procSystem('cls');
    else
        procSystem('clear');
    
    try {
        $user = $client->user->get();
    } catch (\ErrorException $e) {
        error($e);
    }
    
    showUserInfo();
    showMainMenu();
}

/**
 *  System function handler
 */
function procSystem($cmd) {
    $pp = proc_open($cmd, array(STDIN, STDOUT, STDERR), $pipes);
    if(!$pp) return 127;
    return proc_close($pp);
}

main();