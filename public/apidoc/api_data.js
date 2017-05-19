define({ "api": [
  {
    "type": "GET",
    "url": "/inquiry",
    "title": "01.询价单列表(todo)",
    "name": "index",
    "group": "inquiry",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "String",
            "optional": false,
            "field": "authorization-token",
            "description": "<p>token.</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "Array",
            "optional": false,
            "field": "list",
            "description": "<p>询价单号.</p>"
          },
          {
            "group": "Success 200",
            "type": "Number",
            "optional": false,
            "field": "list.id",
            "description": "<p>询价单id.</p>"
          },
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "list.itemCode",
            "description": "<p>料品编号.</p>"
          },
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "list.itemName",
            "description": "<p>料品名称.</p>"
          },
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "list.priceUom",
            "description": "<p>计价单位.</p>"
          },
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "list.tcUom",
            "description": "<p>交易单位.</p>"
          },
          {
            "group": "Success 200",
            "type": "Number",
            "optional": false,
            "field": "list.tcNum",
            "description": "<p>交易数量.</p>"
          },
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "list.reqDate",
            "description": "<p>需求日期.</p>"
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "application/api/controller/Inquiry.php",
    "groupTitle": "inquiry",
    "sampleRequest": [
      {
        "url": "http://atwwg.api.ruitukeji.com/inquiry"
      }
    ]
  },
  {
    "type": "POST",
    "url": "/inquiry/quote",
    "title": "08.报价(todo)",
    "name": "quote",
    "group": "inquiry",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "String",
            "optional": false,
            "field": "authorization-token",
            "description": "<p>token.</p>"
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "Array",
            "optional": false,
            "field": "list",
            "description": "<p>报价列表.</p>"
          },
          {
            "group": "Parameter",
            "type": "Number",
            "optional": false,
            "field": "list.id",
            "description": "<p>询价单id.</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "list.promiseDate",
            "description": "<p>承诺交期.</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "list.price",
            "description": "<p>询价单价.</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "list.remark",
            "description": "<p>备注.</p>"
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "application/api/controller/Inquiry.php",
    "groupTitle": "inquiry",
    "sampleRequest": [
      {
        "url": "http://atwwg.api.ruitukeji.com/inquiry/quote"
      }
    ]
  },
  {
    "type": "POST",
    "url": "/member/login",
    "title": "02.用户登录(todo)",
    "name": "login",
    "group": "member",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "account",
            "description": "<p>账号/手机号/邮箱.</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "password",
            "description": "<p>密码.</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": true,
            "field": "wxOpenid",
            "description": "<p>微信openid.</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": true,
            "field": "pushToken",
            "description": "<p>消息推送token.</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "accessToken",
            "description": "<p>接口调用凭证.</p>"
          },
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "refreshToken",
            "description": "<p>刷新凭证.</p>"
          },
          {
            "group": "Success 200",
            "type": "Number",
            "optional": false,
            "field": "expireTime",
            "description": "<p>有效期.</p>"
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "application/api/controller/Index.php",
    "groupTitle": "member",
    "sampleRequest": [
      {
        "url": "http://atwwg.api.ruitukeji.com/member/login"
      }
    ]
  },
  {
    "type": "POST",
    "url": "/member/login",
    "title": "02.用户登录(todo)",
    "name": "login",
    "group": "member",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "account",
            "description": "<p>账号/手机号/邮箱.</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "password",
            "description": "<p>密码.</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": true,
            "field": "wxOpenid",
            "description": "<p>微信openid.</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": true,
            "field": "pushToken",
            "description": "<p>消息推送token.</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "accessToken",
            "description": "<p>接口调用凭证.</p>"
          },
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "refreshToken",
            "description": "<p>刷新凭证.</p>"
          },
          {
            "group": "Success 200",
            "type": "Number",
            "optional": false,
            "field": "expireTime",
            "description": "<p>有效期.</p>"
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "application/api/controller/Member.php",
    "groupTitle": "member",
    "sampleRequest": [
      {
        "url": "http://atwwg.api.ruitukeji.com/member/login"
      }
    ]
  }
] });
