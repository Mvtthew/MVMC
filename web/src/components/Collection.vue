<template>
	<div class="my-collection-component">
		<loading :visible="loading" :text="'Loading products'"></loading>
		<h1 class="text-center mb-4 mt-5">
			My
			<span v-if="type == 'collection'">collection</span>
			<span v-if="type == 'wishlist'">wishlist</span>
		</h1>
		<div class="row">
			<div class="col-lg-12">
				<div class="row">
					<div class="col-12 p-0">
						<div class="empty mt-4 text-center mb-4" v-if="empty">
							<p>
								Your
								<span v-if="type == 'collection'"
									>collection</span
								>
								<span v-if="type == 'wishlist'">wishlist</span>
								is empty!
							</p>
							<p>
								Try adjusting filters or add new items!
							</p>
							<router-link to="/me/new" class="btn btn-primary">
								<i class="bx bx-plus-circle"></i> Add something
							</router-link>
						</div>
						<div class="filters" v-if="type != 'wishlist'">
							<div
								class="filter"
								:class="{ active: filters.includes('9empty') }"
								@click="toggleFilter('9empty')"
							>
								Empty
							</div>
							<div
								class="filter"
								:class="{
									active: filters.includes('8declutter')
								}"
								@click="toggleFilter('8declutter')"
							>
								Decluttered
							</div>
							<div
								class="filter"
								:class="{ active: filters.includes('7gift') }"
								@click="toggleFilter('7gift')"
							>
								Gifts
							</div>
							<div
								class="filter"
								:class="{ active: filters.includes('1inuse') }"
								@click="toggleFilter('1inuse')"
							>
								In use
							</div>
						</div>
					</div>
					<div
						class="col-lg-4 p-2"
						v-for="(category, index) in collection"
						:key="'category_' + index"
						v-if="category.products.length > 0"
					>
						<div class="category">
							<div
								class="category-header"
								onClick="this.nextElementSibling.toggleAttribute('show')"
							>
								<div class="row">
									<div class="col-10">
										<h4 class="m-0">
											{{ category.category }}
											<small>
												x{{ category.products.length }}
											</small>
										</h4>
									</div>
									<div class="col-2">
										<h3 class="m-0">
											<i class="bx bx-sort"></i>
										</h3>
									</div>
								</div>
							</div>
							<div class="category-body">
								<router-link
									class="product d-flex"
									v-for="product in category.products"
									:key="product._id"
									:to="'/me/p/' + product._id"
								>
									<div
										class="thumbnail"
										v-if="product.thumbnail != ''"
										:style="{
											'background-image':
												'url(' +
												$store.getters.apiUrl +
												'images/' +
												product.thumbnail +
												')'
										}"
										:class="{
											empty: product.status == '9empty'
										}"
									></div>
									<div class="info">
										<p class="brand">{{ product.brand }}</p>
										<p class="name">
											{{ product.name }}
											<span
												class="badge badge-info mr-1"
												v-if="
													product.from_user_id != null
												"
												><i
													class="bx bx-cloud-download"
												></i
											></span>
											<span
												class="badge badge-success"
												v-if="
													product.to_user_id != null
												"
												><i
													class="bx bx-cloud-upload"
												></i
											></span>
										</p>
										<p class="rating">
											<span>
												{{ product.rating }}
											</span>
											<span
												class="bx bxs-star"
												v-for="index in product.rating"
												:key="product.thumbnail + index"
											></span>
										</p>
										<p
											class="pans"
											v-if="type != 'wishlist'"
										>
											<i class="bx bx-download"></i>
											{{ product.pans.done }} /
											{{ product.pans.all }}
										</p>
										<p
											class="uses"
											v-if="type != 'wishlist'"
										>
											<i class="bx bx-check"></i>
											{{ product.uses.length }}
										</p>
									</div>
								</router-link>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import loading from "./Loading.vue";
export default {
	props: ["type"],
	components: { loading },
	data() {
		return {
			empty: false,
			loading: true,
			filters: ["1inuse"],
			productsCounter: 0,
			collection: []
		};
	},
	created() {
		this.getCollection();
	},
	methods: {
		toggleFilter(filter) {
			this.filters = [];
			// if (this.filters.includes(filter)) {
			// 	this.filters.splice(this.filters.indexOf(filter), 1);
			// } else {
			// 	this.filters.push(filter);
			// }
			this.filters.push(filter);
			this.getCollection();
		},
		getCollection() {
			this.loading = true;
			fetch(this.$store.getters.apiUrl + "products/my/" + this.type, {
				method: "POST",
				headers: {
					Authorization: this.$store.getters.token,
					"Content-type": "application/json"
				},
				body: JSON.stringify({ filters: this.filters })
			})
				.then(res => res.json())
				.then(data => {
					this.collection = data;
					this.loading = false;
					this.countProducts();
				});
		},
		countProducts() {
			this.productsCounter = 0;
			this.collection.forEach(category => {
				this.productsCounter += category.products.length;
			});
			if (this.productsCounter == 0) {
				this.empty = true;
			} else {
				this.empty = false;
			}
		}
	}
};
</script>

<style lang="scss" scoped>
.filters {
	margin: 0.5rem;
	display: block;
	.filter {
		padding: 0.5rem;
		border: 1px solid rgba(#000, 0.1);
		border-radius: 1.5rem;
		display: inline-block;
		margin-right: 0.5rem;
		&.active {
			background: #000;
			color: white;
		}
	}
}
.category {
	border: 1px solid rgba(#000, 0.1);
	border-radius: 1.5rem;
	padding: 1rem 0;
	background-color: #fefefe;
	position: relative;

	.category-header {
		position: sticky;
		top: 59px;
		z-index: 1000;
		background-color: #fefefe;
		width: 100%;
		margin-top: 3px;
		padding: 0.5rem 1rem;
		border: {
			bottom: 1px solid rgba(#000, 0.1);
		}
		border-radius: 0 0 1rem 1rem;

        h4, h3 {
            font-size: 1rem;
        }
	}
	.category-body {
		height: 0;
		opacity: 0;
		overflow: hidden;
		transition: 0.3s all;
		&[show] {
			height: 10%;
			opacity: 1;
		}

		.product {
			position: relative;
			padding: 0;
			background-color: #fff;
			border: {
				top: 1px solid rgba(#000, 0.1);
				bottom: 1px solid rgba(#000, 0.1);
			}
			border-radius: 1rem;
			margin-bottom: 1.2rem;
			&:first-child {
				margin-top: 1rem;
			}
			&:last-child {
				margin-bottom: 0;
			}

			.thumbnail {
				width: 30%;
				//height: 100%;
				display: inline-block;
				background-color: teal;
				border-radius: 1rem 0 0 1rem;
				background-position: center;
				background-size: cover;
				background-repeat: no-repeat;
				// &.empty {
				// 	filter: grayscale(100%);
				// }
			}
			.info {
				width: 70%;
				overflow: hidden;
				padding-left: 0.5rem;

				.brand {
					font-weight: 100;
                    font-size: 0.8rem;
					height: 1rem;
					margin: 0;
				}
				.name {
					font-weight: 1000;
					height: 1.3rem;
					overflow: hidden;
					word-wrap: break-word;
					font-size: 1rem;
				}
				.rating {
					margin: 0;

					span {
						margin-right: -3px;
					}
				}
				.pans {
					margin: 0;
				}
				.uses {
					margin: 0;
				}
			}
		}
	}
}
</style>
