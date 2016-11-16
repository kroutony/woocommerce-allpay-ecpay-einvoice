# Woocommerce 歐付寶&綠界科技 電子發票串接外掛

### 系統需求
- Wordpress (支援至4.6.1)
- Woocommerce (支援至2.6.7)

### 安裝步驟

1. 將**allpay-ecpay-e-invoice**資料夾複製至 **{SITE_PATH}/wp-content/plugins/** 下。
2. 將 **allpay-ecpay-e-invoice/languangs/** 下的 **allpay-e-invoice-zh_TW.po** 與 **allpay-e-invoice-zh_TW.mo** 複製至 **{SITE_PATH}/wp-content/languages/plugins/**。
3. 啟用外掛。
4. 至後台Woocommerce子選單的**歐付寶&綠界電子發票** 勾選**啟用**。
5. 選擇**服務來源**。
5. 填寫**捐贈單位**。

###切換至正式環境

1. 將測試模式**取消勾選** 。
2. 輸入**Merchant ID、Hash Key、Hask IV** 。

## 功能介紹

### 使用方式

啟用外掛後，會於結帳頁面產生發票資訊相關欄位。

使用者結帳完之後，在後台的訂單列表中每筆訂單後面會顯示電子發票的欄位，可進行**開立、作廢、重新開立**的動作，每作一次會將發票資訊紀錄至訂單備註下。

### 開立模式

- 自動開立:結帳完之後，即自動開立發票，下列幾種情況皆會觸發：
	- 使用金流服務線上立即付款。
	- 使用貨到付款。
	- 後台手動將訂單狀態變更為由等待付款中變為處理中。
- 手動開立:結帳完之後，不會自動開立發票，需至後台訂單列表開立。

### 修改訂單發票資訊

在編輯訂單的頁面，可以手動修改發票資訊，修改完成再於訂單列表重新開立發票。

**注意，此步驟沒有做前端驗證，亂修改可能導致開立失敗，請參考[電子發票API介接技術文件][1]**

### 目前版本注意事項v1.1.1:

- 僅支援發票類型為"應稅"、"免稅"。
- 當課稅類別為應稅時，在Woocommerce設定->稅金->售價包含稅金設定上，需選擇"是，我將輸入包含稅金的售價"。
- 單一商品課稅類別不影響整體發票課稅類別，僅影響金額計算。

## 測試模式資訊

- Merchant ID:**2000132**
- Hash Key:**ejCk326UnaZWKisg**
- Hash IV:**q9jcZX8Ib9LM8wYk**
- 測試後台帳號:**StageTest**
- 測試後台密碼:**test1234**
- [歐付寶測試後台][2]
- [綠界科技測試後台][3]

## 相關連結

- [綠界科技電子發票API介接技術文件][1]
- [歐付寶電子發票API介接技術文件][1]
- [歐付寶測試後台][2]
- [綠界科技測試後台][3]

[0]: https://www.ecpay.com.tw/Service/API_Dwnld "https://www.ecpay.com.tw/Service/API_Dwnld"
[1]: https://www.allpay.com.tw/Service/API_Dwnld "https://www.allpay.com.tw/Service/API_Dwnld"
[2]: https://vendor-stage.allpay.com.tw "https://vendor-stage.allpay.com.tw"
[3]: https://vendor-stage.ecpay.com.tw "https://vendor-stage.ecpay.com.tw"

## 授權條款

本軟體引用GPL v3.0授權

你可以自由的拿來

- 以免授權金的方式執行、重製與散布
- 使用於商業或個人架站服務

須遵守以下限制

- 不可單獨地將此軟體作直接或修改後販售。
- 須保留原外掛站台連結及原作者名字，可附加資訊。

## TODO

- 其他API串接
- 稅率類別選項
- 翻譯補完
