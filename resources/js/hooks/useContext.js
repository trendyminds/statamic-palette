import { atom, useAtom } from 'jotai'

export const contextState = atom('ACTIONS')

export default () => useAtom(contextState)
