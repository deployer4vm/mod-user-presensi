<template>
  <div>
    <b-container fluid>
      <b-card class="mt-4" no-body header="Notification">
        <button @click="requestNotificationPermission">Izin Notifikasi</button>
        <b-list-group>
          <b-list-group-item
            :to="{ name: item.link_web.route, params: item.link_web.parameter }"
            class="media d-flex align-items-center"
            v-for="item in notif.notification"
            :key="'pagenotif-' + item.id"
          >
            <div
              :class="
                'ui-icon ui-icon-sm ion ion-ios-text border-0 text-white ' +
                (item.read_at == null ? 'bg-danger' : 'bg-secondary')
              "
            ></div>
            <div class="media-body line-height-condenced ml-3">
              <div
                :class="
                  item.read_at == null
                    ? 'text-dark font-weight-bold'
                    : 'text-muted'
                "
              >
                {{ item.data.subject }}
              </div>
              <div class="text-light small mt-1">
                {{ item.data.description }}
              </div>
              <div class="text-light small mt-1">{{ item.created_at }}</div>
            </div>
          </b-list-group-item>
        </b-list-group>
        <div class="float-right m-2 mt-4" v-if="showPagination">
          <b-pagination
            size="sm"
            :total-rows="notif.summary.count"
            v-model="currentPage"
            :per-page="perPage"
            align="center"
          />
        </div>
      </b-card>
    </b-container>
  </div>
</template>

<script>
import { initializeApp } from "firebase/app";
import { getMessaging, getToken } from "firebase/messaging";

export default {
  data() {
    return {
      notif: {
        notification: [],
        summary: {
          count: 0,
          unread_count: 0,
        },
      },
      currentPage: 1,
      perPage: 10,

      messages: [],
      fcmToken: null, // Tambahkan variabel untuk menyimpan token FCM
      app: null, // Tambahkan variabel untuk menyimpan instance Firebase app
    };
  },
  computed: {
    showPagination() {
      return this.notif.summary.count > this.perPage;
    },
    data: {
      get() {
        return this.$store.state.moduserView.dataNotif;
      },
      set(value) {
        this.$store.commit("moduserView/setDataNotif", value);
      },
    },
  },
  watch: {
    currentPage(nV, oV) {
      this.loadNotif((nV - 1) * this.perPage);
    },
    "data.reloadNotif"(v) {
      this.loadNotif();
    },
  },
  created() {
    const firebaseConfig = {
      apiKey: "AIzaSyBFRIbH019yQIKJlflYGJ8E_XqXGwGfEDw",
      authDomain: "smartcoopnotiffcm.firebaseapp.com",
      projectId: "smartcoopnotiffcm",
      storageBucket: "smartcoopnotiffcm.appspot.com",
      messagingSenderId: "864559764072",
      appId: "1:864559764072:web:bab7220c72e900038421dd",
      measurementId: "G-K8QT9BTY1L",
    };

    this.app = initializeApp(firebaseConfig);

    // Initialize Firebase Cloud Messaging and get a reference to the service
    this.messaging = getMessaging(this.app);

    this.requestNotificationPermission(); // Panggil permintaan izin notifikasi saat komponen dibuat
    this.loadNotif(); // Panggil untuk memuat notifikasi
  },
  methods: {
    requestNotificationPermission() {
      if ("Notification" in window) {
        Notification.requestPermission()
          .then((permission) => {
            if (permission === "granted") {
              console.log("Izin notifikasi diberikan");
              this.getFCMToken();
            } else if (permission === "denied") {
              console.warn("Izin notifikasi ditolak");
            }
          })
          .catch((error) => {
            console.error("Error meminta izin notifikasi:", error);
          });
      } else {
        console.warn("Browser tidak mendukung notifikasi");
      }
    },

    getFCMToken() {
      this.messaging
        .getToken()
        .then((currentToken) => {
          if (currentToken) {
            console.log("FCM Token:", currentToken);
            this.fcmToken = currentToken;
          } else {
            console.warn("Tidak ada token registrasi yang tersedia");
          }
        })
        .catch((err) => {
          console.error("Terjadi kesalahan saat mengambil token:", err);
        });
    },
    loadNotif(offset = 0) {
      let filterParams = {
        params: {
          offset: offset,
          limit: this.perPage,
          setRead: true,
        },
      };
      this.LocalApi.get(
        this.AppConfig.endpoint.api.moduser + "/notification",
        filterParams
      ).then((res) => {
        this.notif = res.data.data;
      });
    },
  },
};
</script>
