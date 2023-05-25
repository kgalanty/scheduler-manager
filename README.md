# Schedule Manager installation

1. Copy `WHMCS` content to `MAIN WHMCS DIR`
2. 
```
cd scheduler
npm install
npm run build
```
3. Copy `/scheduler/dist/css` and `/scheduler/dist/js` directories to `/schedule/`
4. Rename `index.html` to `index.php` from `/scheduler/dist`, insert below code on the beginning of the file:

```
<?php require_once('../init.php'); if(!$_SESSION['adminid']){header('Location: ../admin/login.php?redirect=/../schedule/'); exit;}?>
```

5. Upload above file to `/schedule/index.php` path
6. Copy `/scheduleapi/src` content to `MAIN WHMCS dir/schedule/scheduleapi/` content
7. Run `composer update` in `MAIN WHMCS dir/schedule/scheduleapi/`
