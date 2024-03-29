import { atom, selector, useRecoilState, useRecoilValue } from 'recoil'
import { queryState } from './useQuery'

const unfilteredActionsState = atom({
	key: 'unfilteredActionsState',
	default: [],
})

const actionsState = selector({
	key: 'actionsState',
	get: ({ get }) => {
		const query = get(queryState)
		const unfilteredActions = get(unfilteredActionsState)
		return unfilteredActions.filter((action) => {
			return (
				action.name.toLowerCase().includes(query.toLowerCase()) ||
				action.subtitle.toLowerCase().includes(query.toLowerCase())
			)
		})
	},
})

export default function useActions() {
	const [unfilteredActions, setUnfilteredActions] = useRecoilState(
		unfilteredActionsState
	)
	const actions = useRecoilValue(actionsState)

	async function getActions() {
		const response = await fetch('/!/statamic-palette/actions')
		const data = await response.json()
		setUnfilteredActions(data)
	}

	return {
		getActions,
		actions,
	}
}
