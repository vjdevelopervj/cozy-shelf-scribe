
import { useState, useEffect } from 'react';
import { saveToLocalStorage, loadFromLocalStorage, generateId } from '../utils/localStorage';

export const useLibraryData = () => {
  const [members, setMembers] = useState(() => loadFromLocalStorage('library_members', []));
  const [books, setBooks] = useState(() => loadFromLocalStorage('library_books', []));
  const [borrowers, setBorrowers] = useState(() => loadFromLocalStorage('library_borrowers', []));
  const [returns, setReturns] = useState(() => loadFromLocalStorage('library_returns', []));

  // Save to localStorage whenever data changes
  useEffect(() => {
    saveToLocalStorage('library_members', members);
  }, [members]);

  useEffect(() => {
    saveToLocalStorage('library_books', books);
  }, [books]);

  useEffect(() => {
    saveToLocalStorage('library_borrowers', borrowers);
  }, [borrowers]);

  useEffect(() => {
    saveToLocalStorage('library_returns', returns);
  }, [returns]);

  // Member management functions
  const handleAddMember = (memberData: any) => {
    const newMember = {
      ...memberData,
      id: generateId(),
      registrationDate: new Date().toISOString().split('T')[0],
    };
    setMembers([...members, newMember]);
  };

  const handleEditMember = (id: string, memberData: any) => {
    setMembers(members.map(member => 
      member.id === id ? { ...member, ...memberData } : member
    ));
  };

  const handleDeleteMember = (id: string) => {
    setMembers(members.filter(member => member.id !== id));
  };

  // Book management functions
  const handleAddBook = (bookData: any) => {
    const newBook = {
      ...bookData,
      id: generateId(),
    };
    setBooks([...books, newBook]);
  };

  const handleEditBook = (id: string, bookData: any) => {
    setBooks(books.map(book => 
      book.id === id ? { ...book, ...bookData } : book
    ));
  };

  const handleDeleteBook = (id: string) => {
    setBooks(books.filter(book => book.id !== id));
  };

  // Borrower management functions
  const handleAddBorrow = (borrowData: any) => {
    const member = members.find(m => m.id === borrowData.memberId);
    const book = books.find(b => b.id === borrowData.bookId);
    
    if (member && book && book.stock > 0) {
      const newBorrow = {
        ...borrowData,
        id: generateId(),
        memberName: member.name,
        bookTitle: book.title,
      };
      
      setBorrowers([...borrowers, newBorrow]);
      
      setBooks(books.map(b => 
        b.id === book.id ? { ...b, stock: b.stock - 1 } : b
      ));
    }
  };

  const handleDeleteBorrow = (id: string) => {
    const borrowRecord = borrowers.find(b => b.id === id);
    if (borrowRecord && borrowRecord.status === 'Borrowed') {
      const book = books.find(b => b.title === borrowRecord.bookTitle);
      if (book) {
        setBooks(books.map(b => 
          b.id === book.id ? { ...b, stock: b.stock + 1 } : b
        ));
      }
    }
    setBorrowers(borrowers.filter(borrow => borrow.id !== id));
  };

  // Return management functions
  const handleAddReturn = (returnData: any) => {
    const newReturn = {
      ...returnData,
      id: generateId(),
    };
    
    setReturns([...returns, newReturn]);
    
    setBorrowers(borrowers.map(borrow => 
      borrow.id === returnData.borrowId 
        ? { ...borrow, status: 'Returned' as const }
        : borrow
    ));
    
    const borrowRecord = borrowers.find(b => b.id === returnData.borrowId);
    if (borrowRecord) {
      const book = books.find(b => b.title === borrowRecord.bookTitle);
      if (book) {
        setBooks(books.map(b => 
          b.id === book.id ? { ...b, stock: b.stock + 1 } : b
        ));
      }
    }
  };

  const handleDeleteReturn = (id: string) => {
    const returnRecord = returns.find(r => r.id === id);
    if (returnRecord) {
      setBorrowers(borrowers.map(borrow => 
        borrow.id === returnRecord.borrowId 
          ? { ...borrow, status: 'Borrowed' as const }
          : borrow
      ));
      
      const borrowRecord = borrowers.find(b => b.id === returnRecord.borrowId);
      if (borrowRecord) {
        const book = books.find(b => b.title === borrowRecord.bookTitle);
        if (book) {
          setBooks(books.map(b => 
            b.id === book.id ? { ...b, stock: Math.max(0, b.stock - 1) } : b
          ));
        }
      }
    }
    setReturns(returns.filter(returnRec => returnRec.id !== id));
  };

  return {
    // Data
    members,
    books,
    borrowers,
    returns,
    // Member functions
    handleAddMember,
    handleEditMember,
    handleDeleteMember,
    // Book functions
    handleAddBook,
    handleEditBook,
    handleDeleteBook,
    // Borrower functions
    handleAddBorrow,
    handleDeleteBorrow,
    // Return functions
    handleAddReturn,
    handleDeleteReturn,
  };
};
