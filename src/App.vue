<template>
	<div id="content" class="app-wishlist">
		<AppNavigation>
			<AppNavigationNew v-if="!loading"
				:text="t('wishlist', 'New wish')"
				:disabled="false"
				button-id="new-wishlist-button"
				button-class="icon-add"
				@click="newWish" />

			<AppNavigationItem  v-if="!loading"
			:title="t('wishlist', 'Overview')"
				:disabled="false"
				@click="openOverview" />
				
			<ul>
				<AppNavigationItem v-for="wish in wishes"
					:key="wish.id"
					:title="wish.title ? wish.title : t('wishlist', 'New wish')"
					:class="{active: currentWishId === wish.id}"
					@click="openWish(wish)">
					<template slot="actions">
						<ActionButton v-if="wish.id === -1"
							icon="icon-close"
							@click="cancelNewWish(wish)">
							{{ t('wishlist', 'Cancel wish creation') }}
						</ActionButton>
						<ActionButton v-else
							icon="icon-delete"
							@click="deleteWish(wish)">
							{{ t('wishlist', 'Delete wish') }}
						</ActionButton>
					</template>
				</AppNavigationItem>
			</ul>
		</AppNavigation>
		<AppContent>
			<div v-if="currentWish">
				<input ref="title"
					v-model="currentWish.title"
					type="text"
					:disabled="updating">
				<textarea ref="comment" v-model="currentWish.comment" :disabled="updating" />
				<textarea ref="link" v-model="currentWish.link" :disabled="updating" />
				<input type="button"
					class="primary"
					:value="t('wishlist', 'Save')"
					:disabled="updating || !savePossible"
					@click="saveWish">
			</div>
			<div v-else id="emptycontent">
				<div class="icon-file" />
				<h2>{{ t('wishlist', 'Create a wish to get started') }}</h2>
			</div>
		</AppContent>
	</div>
</template>

<script>
import ActionButton from '@nextcloud/vue/dist/Components/ActionButton'
import AppContent from '@nextcloud/vue/dist/Components/AppContent'
import AppNavigation from '@nextcloud/vue/dist/Components/AppNavigation'
import AppNavigationItem from '@nextcloud/vue/dist/Components/AppNavigationItem'
import AppNavigationNew from '@nextcloud/vue/dist/Components/AppNavigationNew'

import axios from '@nextcloud/axios'

export default {
	name: 'App',
	components: {
		ActionButton,
		AppContent,
		AppNavigation,
		AppNavigationItem,
		AppNavigationNew,
	},
	data: function() {
		return {
			wishes: [],
			allWishes: [],
			currentWishId: null,
			updating: false,
			loading: true,
		}
	},
	computed: {
		/**
		 * Return the currently selected wish object
		 * @returns {Object|null}
		 */
		currentWish() {
			if (this.currentWishId === null) {
				return null
			}
			return this.wishes.find((wish) => wish.id === this.currentWishId)
		},

		/**
		 * Returns true if a wish is selected and its title is not empty
		 * @returns {Boolean}
		 */
		savePossible() {
			return this.currentWish && this.currentWish.title !== ''
		},
	},
	/**
	 * Fetch list of wishes when the component is loaded
	 */
	async mounted() {
		try {
			const response = await axios.get(OC.generateUrl('/apps/wishlist/wishes'))
			this.wishes = response.data
		} catch (e) {
			console.error(e)
			OCP.Toast.error(t('wishlist', 'Could not fetch wishes'))
		}
		this.loading = false
	},

	methods: {
		/**
		 * Create a new wish and focus the wish content field automatically
		 * @param {Object} wish Wish object
		 */
		openWish(wish) {
			if (this.updating) {
				return
			}
			this.currentWishId = wish.id
			this.$nextTick(() => {
				this.$refs.content.focus()
			})
		},
		openOverview() {
			this.currentWishId = null
		},
		/**
		 * Action tiggered when clicking the save button
		 * create a new wish or save
		 */
		saveWish() {
			if (this.currentWishId === -1) {
				this.createWish(this.currentWish)
			} else {
				this.updateWish(this.currentWish)
			}
		},
		/**
		 * Create a new wish and focus the wish content field automatically
		 * The wish is not yet saved, therefore an id of -1 is used until it
		 * has been persisted in the backend
		 */
		newWish() {
			if (this.currentWishId !== -1) {
				this.currentWishId = -1
				this.wishes.push({
					id: -1,
					title: '',
					content: '',
				})
				this.$nextTick(() => {
					this.$refs.title.focus()
				})
			}
		},
		/**
		 * Abort creating a new wish
		 */
		cancelNewWish() {
			this.wishes.splice(this.wishes.findIndex((wish) => wish.id === -1), 1)
			this.currentWishId = null
		},
		/**
		 * Create a new wish by sending the information to the server
		 * @param {Object} wish Wish object
		 */
		async createWish(wish) {
			this.updating = true
			try {
				const response = await axios.post(OC.generateUrl(`/apps/wishlist/wishes`), wish)
				const index = this.wishes.findIndex((match) => match.id === this.currentWishId)
				this.$set(this.wishes, index, response.data)
				this.currentWishId = response.data.id
			} catch (e) {
				console.error(e)
				OCP.Toast.error(t('wishlist', 'Could not create the wish'))
			}
			this.updating = false
		},
		/**
		 * Update an existing winewWish on the server
		 * @param {Object} wish Wish object
		 */
		async updateWish(wish) {
			this.updating = true
			try {
				await axios.put(OC.generateUrl(`/apps/wishlist/wishes/${wish.id}`), wish)
			} catch (e) {
				console.error(e)
				OCP.Toast.error(t('wishlist', 'Could not update the wish'))
			}
			this.updating = false
		},
		/**
		 * Delete a wish, remove it from the frontend and show a hint
		 * @param {Object} wish Wish object
		 */
		async deleteWish(wish) {
			try {
				await axios.delete(OC.generateUrl(`/apps/wishlist/wishes/${wish.id}`))
				this.wishes.splice(this.wishes.indexOf(wish), 1)
				if (this.currentWishId === wish.id) {
					this.currentWishId = null
				}
				OCP.Toast.success(t('wishlist', 'Wish deleted'))
			} catch (e) {
				console.error(e)
				OCP.Toast.error(t('wishlist', 'Could not delete the wish'))
			}
		},
	},
}
</script>
<style scoped>
	#app-content > div {
		width: 100%;
		height: 100%;
		padding: 20px;
		display: flex;
		flex-direction: column;
		flex-grow: 1;
	}

	input[type='text'] {
		width: 100%;
	}

	textarea {
		flex-grow: 1;
		width: 100%;
	}
</style>
