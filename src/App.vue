<template>
	<div id="content" class="app-wishlist">
		<AppNavigation>
			<AppNavigationNew v-if="!loading"
				:text="t('wishlist', 'New wish')"
				:disabled="false"
				button-id="new-wishlist-button"
				button-class="icon-add"
				@click="newWish" />

			<div id="app-navigation">
				<ul>
					<li class="icon-star">
						<a href="#" @click="selectUser()">{{ t('wishlist', 'All users') }}</a>
					</li>
					<li
						v-for="user in users"
						:key="user.uid"
						@click="selectUser(user.uid)">
						<!-- <Avatar
							:user="user.uid"
							:displayName="getUser(user.uid).name" /> -->
						<a href="#">
							{{ getUser(user.uid).name }}
						</a>
					</li>
				</ul>
			</div>

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
				<input ref="price"
					v-model="currentWish.price"
					:disabled="updating"
					maxlength="10">
				<label for="userId">{{ t('wishlist', 'For') }}</label>
				<div class="target-user">
					<select v-model="currentWish.userId" :disabled="updating">
						<option
							v-for="user in users"
							:key="user.uid"
							:value="user.uid">
							{{ user.name }}
						</option>
					</select>
				</div>
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
					<div class="list-user">
						<span class="list-user-avatar">
							<Avatar
								:user="list_userId"
								:displayName="getUser(list_userId).name" />
						</span>
						<h2>
							{{ getUser(list_userId).name }}
						</h2>
					</div>
					<div v-for="list_wish in list_wishes"
						:key="list_wish.id"
						class="wish-item">
						<div
							v-if="list_wish.userId !== userId"
							:class="'wish-status ' + ( list_wish.grabbedBy ? (list_wish.grabbedBy === userId ? 'bg-green' : 'bg-red') : '')">
							<span
								v-if="list_wish.grabbedBy && list_wish.grabbedBy !== userId">
								{{ t('wishlist', 'Grabbed by ') }}
								<UserBubble :user="getUser(list_wish.grabbedBy).uid" :display-name="getUser(list_wish.grabbedBy).name">
									{{ getUser(list_wish.grabbedBy).name }}
								</UserBubble>
							</span>
							<span v-else-if="list_wish.grabbedBy === userId">
								{{ t('wishlist', 'Grabbed by you') }}
								<span
									class="button"
									@click="grabWish(list_wish)">
									{{ t('wishlist', 'Ungrab') }}
								</span>
							</span>
							<span v-else>
								{{ t('wishlist', 'Free!') }}
								<span class="button"
									:primary="true"
									@click="grabWish(list_wish)">
									{{ t('wishlist', 'Grab') }}
								</span>
							</span>
						</div>
						<div class="wish-container">
							<div class="wish-details">
								<div class="wish-header">
									<span class="wish-title">
										{{ list_wish.title }}
									</span>
									<div
										v-if="list_wish.price"
										class="price">
										{{ list_wish.price }}
									</div>
								</div>
								<div>
									{{ t('wishlist', 'Added by') }}
									<UserBubble :user="getUser(list_wish.createdBy).uid" :display-name="getUser(list_wish.createdBy).name">
										{{ getUser(list_wish.createdBy).name }}
									</UserBubble>
									on {{ list_wish.createdAt }}
								</div>
								<div v-if="list_wish.link">
									<a
										:href="list_wish.link"
										target="_blank">
										{{ formatUrl(list_wish.link) }}
									</a>
								</div>
								<div v-if="list_wish.comment" class="wish-comment">
									"{{ list_wish.comment }}"
								</div>
							</div>
							<div class="wish-actions">
								<template v-if="list_wish.createdBy === userId || list_wish.grabbedBy === userId">
									<Popover>
										<button slot="trigger" class="icon-more" />
										<template>
											<ul>
												<li
													v-if="list_wish.createdBy === userId"
													class="button"
													@click="openWish(list_wish)">
													{{ t('wishlist', 'Edit') }}
												</li>
												<li
													icon="icon-delete"
													class="button"
													@click="deleteWish(list_wish)">
													{{ t('wishlist', 'Delete') }}
												</li>
											</ul>
										</template>
									</Popover>
								</template>
							</div>
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
import UserBubble from '@nextcloud/vue/dist/Components/UserBubble'
import Popover from '@nextcloud/vue/dist/Components/Popover'
export default {
	name: 'App',
	components: {
		// ActionButton,
		AppContent,
		AppNavigation,
		// AppNavigationItem,
		AppNavigationNew,
		Avatar,
		UserBubble,
		Popover,
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
			users: {},
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

			this.wishes = response.data.wishes

			this.filterWishesByUser()

			this.userId = response.data.userId
			this.users = response.data.users
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
		selectUser(userId) {
			this.filterWishesByUser(userId)
		},
		filterWishesByUser(userId) {
			const wishes = {}

			for (let i = 0; i < this.wishes.length; i++) {
				const wish = this.wishes[i]
				if (userId && wish.userId !== userId) {
					continue
				}
				if (wish.userId in wishes) {
					wishes[wish.userId].push(wish)
				} else {
					wishes[wish.userId] = [wish]
				}
			}

			this.wishesByUser = wishes
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
					userId: '',
				})
				this.$nextTick(() => {
					this.$refs.title.focus()
				})
			}
		},
		getUser(uid) {
			if (this.users[uid] === undefined) {
				console.error('User [' + uid + '] not found in list')
				return {
					'uid': uid,
					'name': uid,
				}
			}

			return this.users[uid]
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
				OCP.Toast.error(t('wishlist', 'Could not update the wish: ' + e.response.data.message))
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
		formatUrl(url) {
			try {
				const uri = new URL(url)

				return uri.hostname
			} catch (TypeError) {
				return url
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
		margin-bottom: 20px;
		border-radius: 5px;
		max-width: 600px;
	}

	.wish-container {
		display: flex;
	}

	.wish-details {
		padding: 10px;
		width: 100%;
	}

	.wish-status {
		border-bottom: 1px solid gray;
		padding: 10px;
	}

	.bg-green {
		background-color: darkseagreen;
	}

	.bg-red {
		background-color: indianred;
	}

	.list-user {
		display: block;
	}

	.list-user-avatar {
		float: left;
		margin-right: 10px;
	}

	.price {
		float: right;
		width: 70px;
	}

	.wish-title {
		width: 100%;
		font-size: 20px;
	}

	a {
		color: #0c76ff;
		text-decoration: underline;
	}

	.wish-comment {
		font-style: italic;
		padding: 5px;
	}
</style>
