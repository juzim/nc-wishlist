<template>
	<div id="content" class="app-wishlist">
		<AppNavigation>
			<AppNavigationNew v-if="!loading"
				:text="t('wishlist', 'New wish')"
				:disabled="false"
				button-id="new-wishlist-button"
				button-class="icon-add"
				@click="newWish" />

			<AppNavigationNew v-if="!loading"
				:text="t('wishlist', 'Overview')"
				:disabled="false"
				@click="openOverview" />

			<!-- <AppNavigationNew v-if="!loading"
				:text="t('wishlist', 'Your wishes')"
				:disabled="false"
				@click="openOwnWishes" /> -->

			<!-- <ul>
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
						<ActionButton v-if="wish.createdBy === userId"
							icon="icon-delete"
							@click="deleteWish(wish)">
							{{ t('wishlist', 'Delete wish') }}
						</ActionButton>
					</template>
				</AppNavigationItem>
			</ul> -->
		</AppNavigation>
		<AppContent>
			<div v-if="currentWish">
				<input ref="title"
					v-model="currentWish.title"
					type="text"
					:disabled="updating">
				<label for="comment">{{ t('wishlist', 'Comment') }}</label>
				<textarea ref="comment" v-model="currentWish.comment" :disabled="updating" />
				<label for="link">{{ t('wishlist', 'Link') }}</label>
				<input ref="link" v-model="currentWish.link" :disabled="updating">
				<label for="price">{{ t('wishlist', 'Price') }}</label>
				<input ref="price" v-model="currentWish.price" :disabled="updating">
				<label for="userId">{{ t('wishlist', 'For') }}</label>
				<input ref="userId" v-model="currentWish.userId" :disabled="updating">
				<input type="button"
					class="primary"
					:value="t('wishlist', 'Save')"
					:disabled="updating || !savePossible"
					@click="saveWish">
				<input type="button"
					class="primary"
					:value="t('wishlist', 'Cancel')"
					:disabled="updating"
					@click="openOverview">
			</div>
			<!-- <div v-else id="emptycontent">
				<div class="icon-file" />
				<h2>{{ t('wishlist', 'Create a wish to get started') }}</h2>
			</div> -->
			<div v-else id="overview">
				<div v-for="(list_wishes, list_userId) in wishesByUser"
					:key="list_userId">
					<span>
						<Avatar :user="list_userId" />
					</span>
					<span>
						{{ list_userId }}
					</span>
					<div v-for="list_wish in list_wishes"
						:key="list_wish.id"
						class="wish-item">
						<div>
							<h3>
								<span>{{ list_wish.title }}</span>
								<a
									v-if="list_wish.user_id === userId || list_wish.createdBy === userId"
									class="button"
									@click="openWish(list_wish)">
									edit
								</a>
								<a
									v-if="list_wish.user_id === userId || list_wish.createdBy === userId"
									class="button"
									icon="icon-delete"
									@click="deleteWish(list_wish)">
									delete
								</a>
							</h3>
						</div>
						<div v-if="list_wish.userId !== userId">
							<div v-if="list_wish.grabbedBy && list_wish.grabbedBy !== userId">
								<input
									type="button"
									:value="t('wishlist', 'Grabbed by ' + list_wish.grabbedBy)"
									:disabled="true"
									@click="grabWish(list_wish)">
							</div>
							<div v-else>
								<input
									type="button"
									:class="!list_wish.grabbedBy ? 'primary' : ''"
									:value="!list_wish.grabbedBy ? t('wishlist', 'Grab') : t('wishlist', 'Ungrab')"
									:disabled="updating"
									@click="grabWish(list_wish)">
							</div>
						</div>
						<div>{{ t('wishlist', 'Added by') }} {{ list_wish.createdBy }} on {{ list_wish.createdAt }}</div>
						<div v-if="list_wish.price" :class="price">
							{{ list_wish.price }}
						</div>
						<div v-if="list_wish.link">
							<a
								:href="list_wish.link"
								target="_blank">
								{{ list_wish.link }}
							</a>
						</div>
						<div v-if="list_wish.comment">
							<textarea ref="comment" v-model="list_wish.comment" readonly="true" />
						</div>
					</div>
				</div>
			</div>
		</AppContent>
	</div>
</template>

<script>
// import ActionButton from '@nextcloud/vue/dist/Components/ActionButton'
import AppContent from '@nextcloud/vue/dist/Components/AppContent'
import AppNavigation from '@nextcloud/vue/dist/Components/AppNavigation'
// import AppNavigationItem from '@nextcloud/vue/dist/Components/AppNavigationItem'
import AppNavigationNew from '@nextcloud/vue/dist/Components/AppNavigationNew'
import axios from '@nextcloud/axios'
import Avatar from '@nextcloud/vue/dist/Components/Avatar'

export default {
	name: 'App',
	components: {
		// ActionButton,
		AppContent,
		AppNavigation,
		// AppNavigationItem,
		AppNavigationNew,
		Avatar,
	},
	data: function() {
		return {
			wishes: [],
			allWishes: [],
			currentWishId: null,
			updating: false,
			loading: true,
			userId: null,
			wishesByUser: {},
			// ownWshes: {},
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
			return this.currentWish && this.currentWish.title !== '' && this.currentWish.user_id !== ''
		},
	},
	/**
	 * Fetch list of wishes when the component is loaded
	 */
	async mounted() {
		try {
			const response = await axios.get(OC.generateUrl('/apps/wishlist/wishes'))
			const wishes = {}

			for (let i = 0; i < response.data.wishes.length; i++) {
				const wish = response.data.wishes[i]
				if (wish.userId in wishes) {
					wishes[wish.userId].push(wish)
				} else {
					wishes[wish.userId] = [wish]
				}
			}

			this.wishesByUser = wishes

			this.wishes = response.data.wishes
			this.userId = response.data.userId
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
		 * Update an existing winewWish on the server
		 * @param {Object} wish Wish object
		 */
		grabWish(wish) {
			if (wish.grabbedBy) {
				wish.grabbedBy = null
			} else {
				wish.grabbedBy = this.userId
			}

			this.updateWish(wish)
			// this.wishesByUser[wish.userId].find()
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
					comment: '',
					price: '',
					targetUser: '',
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
				OCP.Toast.success(t('wishlist', 'Wish created'))
			} catch (e) {
				console.error(e)
				OCP.Toast.error(t('wishlist', 'Could not create the wish'))
			}
			this.updating = false
			this.currentWishId = null
		},
		/**
		 * Update an existing winewWish on the server
		 * @param {Object} wish Wish object
		 */
		async updateWish(wish) {
			this.updating = true
			try {
				await axios.put(OC.generateUrl(`/apps/wishlist/wishes/${wish.id}`), wish)
				this.currentWishId = null
				OCP.Toast.success(t('wishlist', 'Wish updated'))
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

	.wish-item {
		border: 1px solid gray;
		padding: 10px;
		margin-bottom: 20px;
		border-radius: 5px;
	}
</style>
