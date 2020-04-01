import React, { useState, useEffect } from 'react';

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

import { useCancelTokenSource } from '@centreon/ui';

import { getData } from '../../../api';
import {
  labelSomethingWentWrong,
  labelYes,
  labelNo,
} from '../../../translatedLabels';

import { Listing } from '../../../models';
import { Column } from '../..';

const getYesNoLabel = (value): string => (value ? labelYes : labelNo);

const columnMaxWidth = 150;

export interface DetailsTableProps {
  endpoint: string;
  columns: Array<Column>;
}

const DetailsTable = <TDetails extends unknown>({
  endpoint,
  columns,
}: DetailsTableProps): JSX.Element => {
  const [details, setDetails] = useState<TDetails | null>();
  const { cancel, token } = useCancelTokenSource();

  useEffect(() => {
    getData<Listing<TDetails>>({
      endpoint,
      requestParams: { cancelToken: token },
    })
      .then((retrievedDetails) => setDetails(retrievedDetails.result[0]))
      .catch(() => {
        setDetails(null);
      });

    return (): void => cancel();
  }, []);

  const loading = details === undefined;
  const error = details === null;
  const success = !loading && !error;

  const tableMaxWidth = columns.length * columnMaxWidth;

  return (
    <TableContainer component={Paper}>
      <Table size="small" style={{ width: tableMaxWidth }}>
        <TableHead>
          <TableRow>
            {columns.map(({ label }) => (
              <TableCell key={label}>{label}</TableCell>
            ))}
          </TableRow>
        </TableHead>
        <TableBody>
          <TableRow>
            {loading && (
              <TableCell colSpan={columns.length}>
                <Skeleton height={20} animation="wave" />
              </TableCell>
            )}
            {success &&
              columns.map(({ label, getFormattedString }) => (
                <TableCell key={label}>
                  <span>{getFormattedString?.(details)}</span>
                </TableCell>
              ))}
            {error && (
              <TableCell align="center" colSpan={columns.length}>
                <span>{labelSomethingWentWrong}</span>
              </TableCell>
            )}
          </TableRow>
        </TableBody>
      </Table>
    </TableContainer>
  );
};

export { getYesNoLabel };
export default DetailsTable;
