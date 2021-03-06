curl -s 'http://127.0.0.1/lernfunk/plug-ins/matterhorn/matterhorn-queue/' --data 'request={"cmd":"adddata","key":"___",
  "mediapackage":{
    "start":"2010-12-20T12:25:15Z",
    "id":"4b6c48d0-2e4d-40bf-aca3-326cd6ee225e",
    "duration":"235000",
    "title":"i love matterhorn",
    "media":{
      "track":{
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
      }
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
