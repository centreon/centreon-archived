import * as React from 'react';

import {
  TableContainer,
  TableRow,
  Paper,
  Table,
  TableHead,
  TableCell,
  TableBody,
} from '@material-ui/core';
import { Skeleton } from '@material-ui/lab';

import {
  labelSomethingWentWrong,
  labelYes,
  labelNo,
} from '../../../translatedLabels';

import { Listing } from '../../../models';
import { Column } from '../..';

const getYesNoLabel = (value): string => (value ? labelYes : labelNo);

interface DetailsTableColumn extends Column {
  getContent: (details) => string | JSX.Element;
}

export interface DetailsTableProps<TDetails> {
  loading: boolean;
  data: Listing<TDetails> | null | undefined;
  columns: Array<DetailsTableColumn>;
}

const DetailsTable = <TDetails extends unknown>({
  loading,
  data,
  columns,
}: DetailsTableProps<TDetails>): JSX.Element => {
  const error = data === null;
  const success = !loading && !error;

  return (
    <TableContainer component={Paper}>
      <Table size="small">
        <TableHead>
          <TableRow>
            {columns.map(({ label }) => (
              <TableCell key={label}>{label}</TableCell>
            ))}
          </TableRow>
        </TableHead>
        <TableBody>
          {loading && (
            <TableRow>
              <TableCell colSpan={columns.length}>
                <Skeleton height={20} animation="wave" />
              </TableCell>
            </TableRow>
          )}
          {success &&
            data?.result?.map((detail, index) => (
              <TableRow key={index}>
                {columns.map(({ label, getContent }) => (
                  <TableCell key={label}>
                    <span>{getContent?.(detail)}</span>
                  </TableCell>
                ))}
              </TableRow>
            ))}
          {error && (
            <TableRow>
              <TableCell align="center" colSpan={columns.length}>
                <span>{labelSomethingWentWrong}</span>
              </TableCell>
            </TableRow>
          )}
        </TableBody>
      </Table>
    </TableContainer>
  );
};

export { getYesNoLabel };
export default DetailsTable;
