curl -s 'http://127.0.0.1/lernfunk/plug-ins/matterhorn/matterhorn-queue/' --data 'request={"cmd":"adddata","key":"___",
  "mediapackage":{
    "start":"2010-12-20T12:25:15Z",
    "id":"4b6c48d0-2e4d-40bf-aca3-326cd6ee225e",
    "duration":"235000",
    "title":"i love matterhorn",
    "media":{
      "track":[{
        "type":"presenter\/source",
        "id":"1d37ecdf-6344-41f7-b1bd-ab7b59d2bc7e",
        "mimetype":"audio\/mp4",
        "tags":"",
        "url":"http:\/\/opencast.virtuos.uos.de:8080\/files\/mediapackage\/4b6c48d0-2e4d-40bf-aca3-326cd6ee225e\/1d37ecdf-6344-41f7-b1bd-ab7b59d2bc7e\/iLOVE_Matterhorn.mp4",
        "checksum":{
          "type":"md5",
          "$":"b621d5f6587ad875fb9ba25aa56cca33"
        },
        "duration":235000,
        "audio":{
          "id":"audio-1",
          "device":"",
          "encoder":{
            "type":"AAC"
          },
          "bitdepth":16,
          "channels":2,
          "bitrate":91528
        },
        "video":{
          "id":"video-1",
          "device":"",
          "encoder":{
            "type":"AVC"
          },
          "bitrate":383768,
          "framerate":30,
          "resolution":"480x270",
          "scantype":{
            "type":"Progressive"
          }
        }
      },{
        "ref":"track:b3310a51-1734-4105-94a4-909835fe5c67",
        "type":"presenter\/delivery",
        "id":"track-8",
        "mimetype":"audio\/x-adpcm",
        "tags":{
          "tag":["engage","publish"]
        },
        "url":"http:\/\/opencast.virtuos.uos.de:8080\/static\/4b6c48d0-2e4d-40bf-aca3-326cd6ee225e\/b3310a51-1734-4105-94a4-909835fe5c67\/iLOVE_Matterhorn.flv",
        "checksum":{
          "type":"md5",
          "$":"56ff9f082693243675730f857e402a87"
        },
        "duration":235000,
        "audio":{
          "id":"audio-1",
          "device":"",
          "encoder":{
            "type":"ADPCM"
          },
          "bitdepth":16,
          "channels":2,
          "bitrate":93750
        },
        "video":{
          "id":"video-1",
          "device":"",
          "encoder":{
            "type":"H.263"
          },
          "bitrate":195312,
          "framerate":30,
          "resolution":"480x270"
        }
      },{
        "ref":"track:ad0c1172-ad25-48d7-84d3-ebda2f186480",
        "type":"presenter\/delivery",
        "id":"track-9",
        "mimetype":"audio\/m4a",
        "tags":{
          "tag":["atom","publish","rss"]
        },
        "url":"http:\/\/opencast.virtuos.uos.de:8080\/static\/4b6c48d0-2e4d-40bf-aca3-326cd6ee225e\/ad0c1172-ad25-48d7-84d3-ebda2f186480\/iLOVE_Matterhorn.m4a",
        "checksum":{
          "type":"md5",
          "$":"01926e5f0192073a5bb12117fdf90b7d"
        },
        "duration":234985,
        "audio":{
          "id":"audio-1",
          "device":"",
          "encoder":{
            "type":"AAC"
          },
          "bitdepth":16,
          "channels":2,
          "bitrate":187666
        }
      },{
        "ref":"track:566283f9-d1b6-4b12-84ec-ad220ba1d5f0",
        "type":"presenter\/delivery",
        "id":"track-10",
        "mimetype":"video\/x-flv",
        "tags":{
          "tag":["engage","publish"]
        },
        "url":"http:\/\/opencast.virtuos.uos.de:8080\/static\/4b6c48d0-2e4d-40bf-aca3-326cd6ee225e\/566283f9-d1b6-4b12-84ec-ad220ba1d5f0\/iLOVE_Matterhorn.flv",
        "checksum":{
          "type":"md5",
          "$":"aab2610c8991c1940a8be19c98d1bd0f"
        },
        "duration":235067,
        "audio":{
          "id":"audio-1",
          "device":"",
          "encoder":{
            "type":"ADPCM"
          },
          "bitdepth":16,
          "channels":2,
          "bitrate":93750
        },
        "video":{
          "id":"video-1",
          "device":"",
          "encoder":{
            "type":"H.263"
          },
          "bitrate":500000,
          "framerate":15,
          "resolution":"480x270"
        }
      },{
        "ref":"track:6bad3bbc-91a5-42f4-9762-e9c07cb4b71e",
        "type":"presenter\/delivery",
        "id":"track-11",
        "mimetype":"video\/avi",
        "tags":{
          "tag":["atom","publish","rss"]
        },
        "url":"http:\/\/opencast.virtuos.uos.de:8080\/static\/4b6c48d0-2e4d-40bf-aca3-326cd6ee225e\/6bad3bbc-91a5-42f4-9762-e9c07cb4b71e\/iLOVE_Matterhorn.avi",
        "checksum":{
          "type":"md5",
          "$":"18406b50f215670c13eabd3cf5f2f959"
        },
        "duration":235000,
        "audio":{
          "id":"audio-1",
          "device":"",
          "encoder":{
            "type":"MPEG Audio"
          },
          "bitdepth":16,
          "channels":2,
          "bitrate":64000
        },
        "video":{
          "id":"video-1",
          "device":"",
          "encoder":{
            "type":"MPEG-4 Visual"
          },
          "bitrate":208696,
          "framerate":25,
          "resolution":"320x240",
          "scantype":{
            "type":"Progressive"
          }
        }
      },{
        "ref":"track:b3310a51-1734-4105-94a4-909835fe5c67",
        "type":"presenter\/delivery",
        "id":"track-12",
        "mimetype":"audio\/x-adpcm",
        "tags":{
          "tag":["engage","publish"]
        },
        "url":"rtmp:\/\/opencast.virtuos.uos.de\/matterhorn-engage\/4b6c48d0-2e4d-40bf-aca3-326cd6ee225e\/b3310a51-1734-4105-94a4-909835fe5c67\/iLOVE_Matterhorn.flv",
        "checksum":{
          "type":"md5",
          "$":"56ff9f082693243675730f857e402a87"
        },
        "duration":235000,
        "audio":{
          "id":"audio-1",
          "device":"",
          "encoder":{
            "type":"ADPCM"
          },
          "bitdepth":16,
          "channels":2,
          "bitrate":93750
        },
        "video":{
          "id":"video-1",
          "device":"",
          "encoder":{
            "type":"H.263"
          },
          "bitrate":195312,
          "framerate":30,
          "resolution":"480x270"
        }
      },{
        "ref":"track:566283f9-d1b6-4b12-84ec-ad220ba1d5f0",
        "type":"presenter\/delivery",
        "id":"track-13",
        "mimetype":"video\/x-flv",
        "tags":{
          "tag":["engage","publish"]
        },
        "url":"rtmp:\/\/opencast.virtuos.uos.de\/matterhorn-engage\/4b6c48d0-2e4d-40bf-aca3-326cd6ee225e\/566283f9-d1b6-4b12-84ec-ad220ba1d5f0\/iLOVE_Matterhorn.flv",
        "checksum":{
          "type":"md5",
          "$":"aab2610c8991c1940a8be19c98d1bd0f"
        },
        "duration":235067,
        "audio":{
          "id":"audio-1",
          "device":"",
          "encoder":{
            "type":"ADPCM"
          },
          "bitdepth":16,
          "channels":2,
          "bitrate":93750
        },
        "video":{
          "id":"video-1",
          "device":"",
          "encoder":{
            "type":"H.263"
          },
          "bitrate":500000,
          "framerate":15,
          "resolution":"480x270"
        }
      }]
    },
    "metadata":{
      "catalog":[{
        "type":"dublincore\/episode",
        "id":"72113e22-d107-4b3c-9950-54c817f859bc",
        "mimetype":"text\/xml",
        "tags":"",
        "url":"http:\/\/opencast.virtuos.uos.de:8080\/files\/mediapackage\/4b6c48d0-2e4d-40bf-aca3-326cd6ee225e\/72113e22-d107-4b3c-9950-54c817f859bc\/dublincore.xml"
      },{
        "ref":"catalog:72113e22-d107-4b3c-9950-54c817f859bc",
        "type":"dublincore\/episode",
        "id":"catalog-2",
        "mimetype":"text\/xml",
        "tags":{
          "tag":"publish"
        },
        "url":"http:\/\/opencast.virtuos.uos.de:8080\/static\/4b6c48d0-2e4d-40bf-aca3-326cd6ee225e\/72113e22-d107-4b3c-9950-54c817f859bc\/dublincore.xml"
      }]
    },
    "attachments":{
      "attachment":[{
        "ref":"attachment:attachment-2",
        "type":"presenter\/search+preview",
        "id":"attachment-4",
        "mimetype":"image\/jpeg",
        "tags":{
          "tag":["engage","publish"]
        },
        "url":"http:\/\/opencast.virtuos.uos.de:8080\/static\/4b6c48d0-2e4d-40bf-aca3-326cd6ee225e\/attachment-2\/iLOVE_Matterhorn.jpg"
      },{
        "ref":"attachment:attachment-3",
        "type":"presenter\/feed+preview",
        "id":"attachment-5",
        "mimetype":"image\/jpeg",
        "tags":{
          "tag":["atom","publish","rss"]
        },
        "url":"http:\/\/opencast.virtuos.uos.de:8080\/static\/4b6c48d0-2e4d-40bf-aca3-326cd6ee225e\/attachment-3\/iLOVE_Matterhorn.jpg"
      },{
        "ref":"attachment:attachment-1",
        "type":"presenter\/player+preview",
        "id":"attachment-6",
        "mimetype":"image\/jpeg",
        "tags":{
          "tag":["engage","publish"]
        },
        "url":"http:\/\/opencast.virtuos.uos.de:8080\/static\/4b6c48d0-2e4d-40bf-aca3-326cd6ee225e\/attachment-1\/iLOVE_Matterhorn.jpg"
      }]
    }
  }
}'