<template>
	<div class="my-collection">
		<collection :type="'collection'"></collection>
	</div>
</template>

<script>
import collection from "../../components/Collection.vue";
export default {
	components: { collection }
};
</script>
