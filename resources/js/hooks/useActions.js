import { atom, useAtom } from 'jotai'
// import { atom as jotaiAtom } from 'jotai'
import { useMemo } from 'react'
import { queryState } from './useQuery'

const unfilteredActionsState = atom([])

// Jotai does not have selectors, so we use useMemo for derived state

export default function useActions() {
	const [unfilteredActions, setUnfilteredActions] = useAtom(
		unfilteredActionsState
	)
	const [query] = useAtom(queryState)
	const actions = useMemo(() => {
		return unfilteredActions.filter((action) => {
			return (
				action.name.toLowerCase().includes(query.toLowerCase()) ||
				action.subtitle.toLowerCase().includes(query.toLowerCase())
			)
		})
	}, [unfilteredActions, query])

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
