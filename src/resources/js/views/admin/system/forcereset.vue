<template>
    <div>
        <h3 class="mt-4 text-center">Reseting apps, please wait...</h3>
    </div>
</template>

<script>
export default {
    name: "moduser-forcelogout",
    created() {
        var that = this;
        setTimeout(function () {
            that.UserAuth.logoutAndReset();
        }, 1000);
    }
}
</script>