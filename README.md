# Magelearn Order Popup

Magento 2 module for sending WhatsApp order notifications through configurable WhatsApp providers.

The module currently supports **WaAPI.app** and is designed with a provider-independent architecture so additional providers such as **Twilio** or **Meta Cloud API** can be added later without changing the order notification flow.

---

## Features

- Send WhatsApp notification automatically after order placement.
- Send WhatsApp notification manually from the Magento Admin order view.
- Display a Send WhatsApp modal on the Admin order page.
- Pre-populate the customer's phone number in the modal.
- Configure the WhatsApp message template from Magento Admin.
- Support dynamic order variables in the message template.
- Support store-specific configuration.
- Provider-independent WhatsApp service architecture.
- WaAPI authentication using encrypted Magento system configuration.
- Configurable default country code.
- Admin ACL permissions for configuration and manual sending.
- Extensible architecture for additional WhatsApp providers.

---

# Module Information

| Item | Value |
|---|---|
| Module Name | `Magelearn_OrderPopup` |
| Vendor | `Magelearn` |
| Module | `OrderPopup` |
| Current Provider | WaAPI |
| Magento Area | Adminhtml / Global |
| PHP | 8.x |
| Architecture | Provider-independent |

---

# Installation

Place the module under:

```text
app/code/Magelearn/OrderPopup
```

Run:

```bash
bin/magento module:enable Magelearn_OrderPopup
```

Then:

```bash
bin/magento setup:upgrade
```

For production mode:

```bash
bin/magento setup:di:compile
bin/magento setup:static-content:deploy -f
bin/magento cache:flush
```

---

# Module Structure

The important files are organized as follows:

```text
Magelearn/OrderPopup/
├── Api/
│   └── WhatsAppServiceInterface.php
│
├── Block/
│   └── Adminhtml/
│       ├── Order/
│       │   └── ModalBox.php
│       └── System/
│           └── Config/
│               └── MessageTemplate.php
│
├── Controller/
│   └── Adminhtml/
│       └── Order/
│           └── Order.php
│
├── Model/
│   ├── Config.php
│   ├── MessageTemplate.php
│   ├── Config/
│   │   └── Source/
│   │       └── Provider.php
│   └── WhatsApp/
│       ├── ServiceFactory.php
│       └── WaApiService.php
│
├── Observer/
│   └── SendOrderWhatsApp.php
│
├── Plugin/
│   └── Magento/
│       └── Sales/
│           └── Block/
│               └── Adminhtml/
│                   └── Order/
│                       └── View.php
│
├── etc/
│   ├── acl.xml
│   ├── adminhtml/
│   │   ├── routes.xml
│   │   ├── system.xml
│   │   └── ...
│   ├── di.xml
│   ├── events.xml
│   └── module.xml
│
└── view/
    └── adminhtml/
        ├── layout/
        │   └── sales_order_view.xml
        ├── templates/
        │   └── order/
        │       └── modalbox.phtml
        └── web/
            └── css/
                └── source/
                    └── _module.less
```

---

# Architecture

The module uses a provider-independent architecture.

```text
                         Order Placement
                               |
                               v
                    SendOrderWhatsApp Observer
                               |
                               v
                         ServiceFactory
                               |
                  +------------+------------+
                  |                         |
                  v                         v
             WaApiService              Future Provider
                  |                  Twilio / Meta / etc.
                  v
              WhatsApp API
```

The same service architecture is used for manual messages from the Magento Admin.

```text
Magento Admin Order View
          |
          v
     Send WhatsApp
          |
          v
      ModalBox
          |
          v
   Admin Controller
          |
          v
    ServiceFactory
          |
          v
     WaApiService
          |
          v
       WaAPI.app
```

---

# WhatsApp Service Interface

The provider contract is defined in:

```text
Api/WhatsAppServiceInterface.php
```

The interface defines the common operation:

```php
public function sendMessage(
    string $phoneNumber,
    string $message,
    ?int $storeId = null
): bool;
```

This allows the application to remain independent of the actual WhatsApp provider.

For example, future implementations can include:

```text
WaApiService
TwilioService
MetaCloudApiService
```

without changing the Observer or Admin Controller.

---

# Provider Factory

The provider is selected using:

```text
Model/WhatsApp/ServiceFactory.php
```

The factory reads the configured provider:

```text
magelearn_orderpopup/whatsapp/provider
```

Currently:

```text
waapi
```

is supported.

Future providers can be added:

```text
waapi
twilio
meta
```

The order notification flow does not need to know which provider is being used.

---

# WaAPI Configuration

Navigate to:

```text
Stores
    → Configuration
        → Sales
            → WhatsApp Order Notification
```

The configuration contains the following sections.

## WhatsApp

### Enable WhatsApp Notification

Enable or disable the module.

```text
Yes
No
```

### Send Automatically After Order Placement

Controls whether the WhatsApp notification is automatically sent after an order is placed.

```text
Yes
No
```

### WhatsApp Provider

Select the provider.

Currently:

```text
WaAPI
```

### Default Country Code

Example:

```text
91
```

Do not include the `+` character.

For example:

```text
91
```

instead of:

```text
+91
```

The country code is used when the customer's phone number does not already contain it.

---

# WaAPI Configuration

The WaAPI configuration contains:

```text
WaAPI Instance ID
WaAPI API Token
```

Example:

```text
Instance ID:
106762
```

The API token is stored using Magento's encrypted configuration backend.

The token is retrieved through `Config.php` and decrypted using Magento's `EncryptorInterface`.

The actual token must never be hard-coded in the source code.

---

# Message Template

The order message is configurable from:

```text
Stores
    → Configuration
        → Sales
            → WhatsApp Order Notification
                → Order Message Template
```

The message template supports dynamic variables.

The variables are replaced automatically when the message is generated.

---

# Available Message Variables

The following variables are supported:

| Variable | Description |
|---|---|
| `{{customer_name}}` | Customer first name and last name |
| `{{increment_id}}` | Magento order increment ID |
| `{{order_total}}` | Order grand total |
| `{{currency}}` | Order currency code |
| `{{store_name}}` | Magento store name |
| `{{shipping_method}}` | Selected shipping method |
| `{{payment_method}}` | Selected payment method |
| `{{billing_address}}` | Customer billing address |
| `{{shipping_address}}` | Customer shipping address |

The Admin configuration displays these variables in a dedicated **Available Variables** section below the message template.

---

# Recommended Message Template

The following template can be used as the default message:

```text
Hello {{customer_name}},

Thank you for your order with {{store_name}}.

Your order *#{{increment_id}}* has been successfully placed.

*Order Total:* {{order_total}} {{currency}}
*Shipping Method:* {{shipping_method}}
*Payment Method:* {{payment_method}}

*Billing Address:*
{{billing_address}}

*Shipping Address:*
{{shipping_address}}

We appreciate your business and will keep you updated regarding your order.

Thank you,
{{store_name}}
```

WhatsApp supports `*text*` formatting for bold text.

For example:

```text
*Order Total:*
```

will be displayed as:

**Order Total:**

---

# How Message Variables Work

The message variables are placeholders.

For example:

```text
Hello {{customer_name}}
```

The module retrieves the customer information from the Magento order.

If the customer's name is:

```text
John Smith
```

the final message becomes:

```text
Hello John Smith
```

Similarly:

```text
{{increment_id}}
```

could become:

```text
000000003
```

The replacement is performed by:

```text
Model/MessageTemplate.php
```

using PHP's `strtr()` function.

Example:

```php
return strtr(
    $template,
    [
        '{{customer_name}}' => $customerName,
        '{{increment_id}}' => (string) $order->getIncrementId(),
        '{{order_total}}' => (string) $order->getGrandTotal(),
        '{{currency}}' => (string) $order->getOrderCurrencyCode(),
        '{{store_name}}' => (string) $order->getStore()->getName(),
        '{{shipping_method}}' => $this->getShippingMethod($order),
        '{{payment_method}}' => $this->getPaymentMethod($order),
        '{{billing_address}}' => $this->getBillingAddress($order),
        '{{shipping_address}}' => $this->getShippingAddress($order),
    ]
);
```

---

# Centralized Message Generation

The message generation logic is intentionally centralized in:

```text
Model/MessageTemplate.php
```

This avoids duplicating the variable replacement logic.

Both automatic and manual messages use the same class:

```text
                 MessageTemplate
                       |
             +---------+---------+
             |                   |
             v                   v
          Observer             ModalBox
             |                   |
             v                   v
       Automatic Send       Manual Send
```

This ensures that the same Admin-configured template and the same variables are used in both flows.

---

# Automatic WhatsApp Notification

The automatic notification is triggered by:

```text
sales_order_place_after
```

The observer is:

```text
Observer/SendOrderWhatsApp.php
```

The flow is:

```text
Order Placed
    |
    v
sales_order_place_after
    |
    v
SendOrderWhatsApp
    |
    +-- Check module enabled
    |
    +-- Check automatic sending enabled
    |
    +-- Get order phone number
    |
    +-- Generate message
    |
    +-- Resolve WhatsApp provider
    |
    +-- Send WhatsApp message
```

The observer uses the order's shipping telephone number first.

If the shipping address does not contain a phone number, the billing address telephone number is used.

---

# Manual WhatsApp Notification

A **Send WhatsApp** button is added to the Magento Admin order view.

The button opens the WhatsApp modal.

```text
Sales
  → Orders
      → View Order
          → Send WhatsApp
```

The modal contains:

```text
Phone Number
Message
```

The phone number is pre-populated from the order.

The message is generated using the same:

```text
Model/MessageTemplate.php
```

class used by the automatic notification.

The administrator can modify the message before sending it.

---

# Admin Modal Architecture

The modal is rendered using Magento layout XML:

```text
view/adminhtml/layout/sales_order_view.xml
```

The block is:

```text
Block/Adminhtml/Order/ModalBox.php
```

The template is:

```text
view/adminhtml/templates/order/modalbox.phtml
```

The Plugin only adds the Admin button.

The Plugin does not inject the modal HTML.

This separation keeps the Admin order view implementation cleaner.

---

# Admin Controller

The manual send request is handled by:

```text
Controller/Adminhtml/Order/Order.php
```

The controller:

1. Loads the order.
2. Reads the phone number.
3. Reads the message.
4. Resolves the configured provider.
5. Sends the message.
6. Returns a JSON response.

Example response:

```json
{
    "success": true,
    "message": "WhatsApp message sent successfully."
}
```

---

# ACL

The module defines two ACL resources.

## Configuration

```text
Magelearn_OrderPopup::config
```

Used for:

```text
Stores → Configuration → WhatsApp Order Notification
```

## Send WhatsApp

```text
Magelearn_OrderPopup::send_whatsapp
```

Used for manually sending WhatsApp messages from the Admin order view.

The ACL file is located at:

```text
etc/acl.xml
```

---

# Routes

The Admin controller uses the following route:

```text
orderpopup/order/order
```

The route is configured in:

```text
etc/adminhtml/routes.xml
```

The route is required because the module contains a custom Admin controller.

---

# Configuration Scope

The WhatsApp configuration supports:

```text
Default
Website
Store View
```

Configuration values are retrieved using:

```php
ScopeInterface::SCOPE_STORE
```

The order's store ID is passed when generating the message and resolving the provider configuration.

This allows different stores to use different:

- WhatsApp providers
- WaAPI instance IDs
- API tokens
- country codes
- message templates

---

# Phone Number Handling

The module removes non-numeric characters from the phone number.

For example:

```text
+91 96627 82488
```

becomes:

```text
919662782488
```

The WhatsApp chat ID is then generated as:

```text
919662782488@c.us
```

If a configured country code is required, it is automatically added when the phone number does not already contain it.

---

# WaAPI Request

The module sends the request to:

```text
https://waapi.app/api/v1/instances/{instance_id}/client/action/send-message
```

The request contains:

```json
{
    "chatId": "919662782488@c.us",
    "message": "Hello John Smith..."
}
```

The request uses:

```http
Authorization: Bearer {API_TOKEN}
Content-Type: application/json
```

---

# WaAPI Instance Status

The WaAPI instance must be in the:

```text
ready
```

state before messages can be sent.

If the instance is authenticated but not ready, WaAPI can return:

```text
HTTP 409
```

with a response similar to:

```text
Instance is not ready (status: authenticated).
Please wait until the instance is in ready status before sending messages.
```

This is a WaAPI instance-state issue rather than a Magento CURL or authentication issue.

---

# API Token Security

The API token is configured through Magento Admin and stored using Magento's encrypted configuration backend.

The token should never be:

- Hard-coded in PHP.
- Committed to Git.
- Printed in logs.
- Returned in API responses.
- Included in exception messages.

For diagnostic logging, only a boolean such as:

```php
'token_configured' => $apiToken !== ''
```

should be logged.

---

# Troubleshooting

## 401 Unauthenticated

Example:

```text
HTTP 401
Unauthenticated.
```

Check:

1. WaAPI API token.
2. WaAPI instance ID.
3. Magento configuration scope.
4. Token decryption.
5. Whether the token was accidentally changed or expired.

The API token retrieved by Magento should be the actual WaAPI token, not the encrypted Magento configuration value.

---

## 409 Instance Is Not Ready

Example:

```text
HTTP 409

Instance is not ready
(status: authenticated)
```

This indicates that the WaAPI instance is authenticated but has not reached the `ready` state.

Wait for the instance to become ready and retry the request.

No Magento CURL changes are normally required for this condition.

---

## Message Variables Are Not Replaced

If a message contains:

```text
Hello {{customer_name}}
```

instead of:

```text
Hello John Smith
```

check:

```text
Model/MessageTemplate.php
```

and make sure the variable exists in the replacement array.

The Admin template variable must exactly match the supported placeholder.

For example:

```text
{{customer_name}}
```

is different from:

```text
{{customer}}
```

---

## Modal Does Not Open

Verify that:

```text
#popup-modal
```

exists on the Admin order page.

The modal block should be loaded through:

```text
view/adminhtml/layout/sales_order_view.xml
```

The Plugin should only add the button and should not inject the modal HTML through `afterToHtml()`.

---

# Cache and Compilation

After changing PHP classes or dependency injection configuration:

```bash
rm -rf generated/code generated/metadata
bin/magento setup:di:compile
bin/magento cache:clean
```

For Admin layout, template, or LESS changes, also clear the relevant caches:

```bash
bin/magento cache:clean layout block_html
```

In production mode, deploy static content when required:

```bash
bin/magento setup:static-content:deploy -f
```

---

# Logging

During development, logs can be found in:

```text
var/log/system.log
```

Important diagnostic messages include:

```text
WaAPI configuration loaded.
WaAPI request prepared.
WaAPI response received.
WaAPI request failed.
```

Avoid logging sensitive information such as the complete API token.

---

# Future Provider Support

The architecture is designed to support additional WhatsApp providers.

Potential future implementations:

```text
Model/WhatsApp/WaApiService.php
Model/WhatsApp/TwilioService.php
Model/WhatsApp/MetaCloudApiService.php
```

The factory can then select the implementation:

```text
                    ServiceFactory
                         |
             +-----------+-----------+
             |           |           |
             v           v           v
           WaAPI       Twilio      Meta
```

The Observer and Admin Controller remain unchanged because they depend on:

```text
WhatsAppServiceInterface
```

rather than a specific provider implementation.

---

# Development Guidelines

The module should follow Magento 2 development standards.

Recommended practices:

- Use dependency injection.
- Avoid direct ObjectManager usage.
- Use service contracts where appropriate.
- Keep provider-specific logic inside provider services.
- Keep message-template processing separate from transport logic.
- Keep configuration access inside `Config.php`.
- Do not hard-code credentials.
- Do not log API tokens.
- Use strict typing.
- Maintain PHPCS compliance.
- Keep Admin presentation logic separate from business logic.

---

# Example Final WhatsApp Message

With the configured template:

```text
Hello {{customer_name}},

Thank you for your order with {{store_name}}.

Your order *#{{increment_id}}* has been successfully placed.

*Order Total:* {{order_total}} {{currency}}
*Shipping Method:* {{shipping_method}}
*Payment Method:* {{payment_method}}

*Billing Address:*
{{billing_address}}

*Shipping Address:*
{{shipping_address}}

We appreciate your business and will keep you updated regarding your order.

Thank you,
{{store_name}}
```

The customer could receive:

```text
Hello John Smith,

Thank you for your order with SupplyBC Store.

Your order #000000003 has been successfully placed.

Order Total: 125.00 CAD
Shipping Method: Standard Shipping
Payment Method: Credit Card

Billing Address:
John Smith
123 Main Street
Vancouver, BC V5K 0A1
CA
+1 604 123 4567

Shipping Address:
John Smith
123 Main Street
Vancouver, BC V5K 0A1
CA
+1 604 123 4567

We appreciate your business and will keep you updated regarding your order.

Thank you,
SupplyBC Store
```

---

# Summary

`Magelearn_OrderPopup` provides a reusable Magento 2 architecture for WhatsApp order notifications.

The key design principles are:

```text
Configuration
     |
     v
MessageTemplate
     |
     +----------------------+
     |                      |
     v                      v
Automatic Notification   Manual Notification
     |                      |
     v                      v
Observer                  Admin Modal
     |                      |
     +----------+-----------+
                |
                v
         ServiceFactory
                |
                v
          WhatsApp Service
                |
                v
             WaAPI
```

The message template, dynamic variables, provider selection, and WhatsApp transport are intentionally separated so the module can be extended without tightly coupling business logic to a specific WhatsApp provider.
