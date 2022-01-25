import * as React from 'react';

import { useUpdateAtom } from 'jotai/utils';
import { useAtom } from 'jotai';

import { limitAtom, pageAtom } from './listingAtoms';

export interface ListingState {
  page?: number;
  setLimit: (limit: React.SetStateAction<number>) => void;
  setPage: (page: React.SetStateAction<number | undefined>) => void;
}

const useListing = (): ListingState => {
  const [page, setPage] = useAtom(pageAtom);
  const setLimit = useUpdateAtom(limitAtom);

  return {
    page,
    setLimit,
    setPage,
  };
};

export default useListing;
