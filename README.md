# JSON-Content of the API calls

## Create a new user

Call the `/simpleauth/create` route with a `POST` request to create a new user.

### Request

You need to provide valid JSON in the request body, which contains at least the `nickname` field:

```
{
"nickname":"habasch"
}
```

Your generated user is in this case completely anonymous. You have however no way of recovering a lost token.

If you additionnaly provide an email address, you can always recover your token, it is then sent to your email address.

```
{
"nickname":"habasch",
"email":"habasch@mail.com"
}
```

Note that if you create a user with an email address for which already a user exists, the token remains the same but the nickname is updated.

### Successful Response

You will receive a JSON-formatted response containing the status `ok` and the generated token:

```
{
"status":"ok",
"token":"GoNPDra"
}
```

### Error Codes

On every error, you receive a JSON-formatted resposne containing the status `nok` and the error message:
- INVALID_JSON Your request body does contain invalid json.
- NO_NICKNAME You did not provide a nickname field in your request.




## Update the email address of an existing user

Call the `/simpleauth/updatemail` route with a `POST` request to update the email address of an existing user.

### Request

You need to provide valid JSON in the request body, which contains at least the `currentmail` and the `newmail` field:

```
{
"currentmail":"habasch@mail.com",
"newmail":"johann@mail.com"
}
```


### Successful Response

You will receive a JSON-formatted response containing the status `ok` and the (existing) token:

```
{
"status":"ok",
"token":"GoNPDra"
}
```

### Error Codes

On every error, you receive a JSON-formatted resposne containing the status `nok` and the error message:
- INVALID_JSON Your request body does contain invalid json.
- NO_CURRENTMAIL You did not provide a currentmail field.
- NO_NEWMAIL You did not provide a newmail field.
- NO_USER There is no existing user with the currentmail address you provided.


## Resend the token of an existing user to her email address

Call the `/simpleauth/resend` route with a `POST` request to resend a users token to its email address.

### Request

You need to provide valid JSON in the request body, which contains at least the `currentmail` and the `newmail` field:

```
{
"email":"habasch@mail.com"
}
```


### Successful Response

You will receive a JSON-formatted response containing the status `ok` if sending the mail was successful.

```
{
"status":"ok"
}
```

Note that this does not guarantee that the mail address provided by the user was in fact valid and the mail could be delivered.

### Error Codes

On every error, you receive a JSON-formatted resposne containing the status `nok` and the error message:
- INVALID_JSON: Your request body does contain invalid json.
- NO_EMAIL: You did not provide an email field.
- NO_USER: There is no existing user with the currentmail address you provided.



