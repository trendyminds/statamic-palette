import { atom, useAtom } from 'jotai'

export const openState = atom(false)

export default () => useAtom(openState)
