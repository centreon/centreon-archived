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

const defaultColumnWidth = 100;

interface DetailsTableColumn extends Column {
  getContent: (details) => string | JSX.Element;
}

export interface DetailsTableProps {
  endpoint: string;
  columns: Array<DetailsTableColumn>;
}

const DetailsTable = <TDetails extends unknown>({
  loading,
  data,
  columns,
}: DetailsTableProps): JSX.Element => {
  const error = data === null;
  const success = !loading && !error;

  const tableWidth = columns.reduce(
    (totalWidth, { width = defaultColumnWidth }) => totalWidth + width,
    0,
  );

  return (
    <TableContainer component={Paper} style={{ width: tableWidth }}>
      <Table size="small">
        <TableHead>
          <TableRow>
            {columns.map(({ label, width = defaultColumnWidth }) => (
              <TableCell key={label} style={{ width }}>
                {label}
              </TableCell>
            ))}
          </TableRow>
        </TableHead>
        <TableBody>
          {data?.result?.map((detail, index) => (
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
