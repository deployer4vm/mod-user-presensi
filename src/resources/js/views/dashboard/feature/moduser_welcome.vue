<template>  
    <div>
        <div class="text-center d-flex flex-column align-items-center py-3">
            <h4 class="font-weight-normal d-inline-block px-3 mb-2 pb-2 border-bottom w-auto" style="line-height: 1.5;">
                Hai!, <span class="font-weight-bold">{{ user }}</span> <br>
                {{ Trans.get('coop_dashboard.label.welcome_text') }} di {{ activeKoperasi.domain }}
            </h4>
            <div class="text-secondary my-2">
                <span class="font-weight-normal">{{ moment().format('llll') }}</span>
            </div>
        </div>        
    </div>
</template>
<script>
export default {
    name: "moduser-dashboard-feature-welcome",
    data: () => ({

    }),
    computed: {        
        // shared view data
        viewData: {
            get() {
                return this.$store.state.moduserView.dataDashboard;
            },
            set(value) {
                this.$store.commit("moduserView/setDataDashboard", value);
            }
        },
        featureData: {
            get() {
                return this.viewData.featureData['moduser.welcome'];
            },
            set(value) {
                this.viewData.featureData['moduser.welcome'] = value;
            }
        },
        //---------        
        user() {
            return this.UserAuth.getUser('name')
        },
        
    }
}
</script>