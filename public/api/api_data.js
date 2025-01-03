define({ "api": [
  {
    "type": "GET",
    "url": "/api/2021091001",
    "title": "展示用户签到页面",
    "group": "签到",
    "description": "<p>展示用户签到页面接口</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "Int",
            "optional": false,
            "field": "uid",
            "description": "<p>用户名</p>"
          },
          {
            "group": "Parameter",
            "type": "Int",
            "optional": false,
            "field": "app_id",
            "description": "<p>应用id</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "请求参数示例",
          "content": "{\n \"uid\": \"2\",\n \"app_id\": \"2\",\n}",
          "type": "json"
        }
      ]
    },
    "error": {
      "fields": {
        "Error 4xx": [
          {
            "group": "Error 4xx",
            "type": "Int",
            "optional": false,
            "field": "Code",
            "description": "<p>-100</p>"
          },
          {
            "group": "Error 4xx",
            "type": "String",
            "optional": false,
            "field": "message",
            "description": "<p>参数验证失败</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "错误示例",
          "content": "{\n  \"code\":-100\n  \"message\": \"参数验证失败\"\n}",
          "type": "json"
        }
      ]
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "code",
            "description": "<p>200</p>"
          },
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "message",
            "description": "<p>操作成功</p>"
          },
          {
            "group": "Success 200",
            "type": "Int",
            "optional": false,
            "field": "today_sign",
            "description": "<p>今日是否签到 ：1 为已签到；0为未签到</p>"
          },
          {
            "group": "Success 200",
            "type": "Int",
            "optional": false,
            "field": "sign_count",
            "description": "<p>连续签到天数</p>"
          },
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "plan",
            "description": "<p>所采用计划</p>"
          },
          {
            "group": "Success 200",
            "type": "Jsom",
            "optional": false,
            "field": "content",
            "description": "<p>返回json值</p>"
          },
          {
            "group": "Success 200",
            "type": "Int",
            "optional": false,
            "field": "day",
            "description": "<p>第几天</p>"
          },
          {
            "group": "Success 200",
            "type": "Int",
            "optional": false,
            "field": "money",
            "description": "<p>当天获取的最大金额</p>"
          },
          {
            "group": "Success 200",
            "type": "Int",
            "optional": false,
            "field": "is_sign",
            "description": "<p>当天是否签到 0未签到，1已签到</p>"
          },
          {
            "group": "Success 200",
            "type": "Int",
            "optional": false,
            "field": "status",
            "description": "<p>状态 0不可领取，1今天可领取，2已领取，3明天领取</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "正确示例",
          "content": "\n{\"data\":{\"today_sign\":0,\"sign_count\":1,\"plan\":\"a\",\"content\":[{\"day\":\"1\",\"money\":\"1\",\"is_sign\":1,\"status\":2},{\"day\":\"2\",\"money\":\"2\",\"is_sign\":0,\"status\":0},{\"day\":\"3\",\"money\":\"2\",\"is_sign\":0,\"status\":3},{\"day\":\"4\",\"money\":\"3\",\"is_sign\":0,\"status\":0},{\"day\":\"5\",\"money\":\"5\",\"is_sign\":0,\"status\":0},{\"day\":\"6\",\"money\":\"8\",\"is_sign\":0,\"status\":0},{\"day\":\"7\",\"money\":\"10\",\"is_sign\":0,\"status\":0}]},\"code\":200,\"msg\":\"\\u64cd\\u4f5c\\u6210\\u529f\"}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "app/Http/Controllers/Api/SignController.php",
    "groupTitle": "签到",
    "name": "GetApi2021091001"
  },
  {
    "type": "POST",
    "url": "/api/2021091002",
    "title": "用户签到",
    "group": "签到",
    "description": "<p>用户签到</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "Int",
            "optional": false,
            "field": "uid",
            "description": "<p>用户名</p>"
          },
          {
            "group": "Parameter",
            "type": "Int",
            "optional": false,
            "field": "app_id",
            "description": "<p>应用id</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "请求参数示例",
          "content": "{\n \"uid\": \"2\",\n \"app_id\": \"2\",\n}",
          "type": "json"
        }
      ]
    },
    "error": {
      "fields": {
        "Error 4xx": [
          {
            "group": "Error 4xx",
            "type": "Int",
            "optional": false,
            "field": "Code",
            "description": "<p>-100</p>"
          },
          {
            "group": "Error 4xx",
            "type": "String",
            "optional": false,
            "field": "message",
            "description": "<p>参数验证失败</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "错误示例",
          "content": "{\n  \"code\":-100\n  \"message\": \"参数验证失败\"\n}",
          "type": "json"
        }
      ]
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "code",
            "description": "<p>200</p>"
          },
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "message",
            "description": "<p>操作成功</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "正确示例",
          "content": "{\"data\":{},\"code\":200,\"msg\":\"操作成功\"}}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "app/Http/Controllers/Api/SignController.php",
    "groupTitle": "签到",
    "name": "PostApi2021091002"
  }
] });
